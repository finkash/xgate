<?php

namespace Database\Seeders;

use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostMedia;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use App\Domain\IdentityAndAccess\Enums\SeedFirstName;
use App\Domain\IdentityAndAccess\Enums\SeedLastName;
use App\Domain\IdentityAndAccess\Models\Follow;
use App\Domain\IdentityAndAccess\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SocialMediaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->resetSocialTables();

        $targetUsers = 12;
        $existingUsers = User::query()->count();
        $missingUsers = max(0, $targetUsers - $existingUsers);

        if ($missingUsers > 0) {
            $this->createUsers($missingUsers);
        }

        $participants = User::query()->orderBy('id')->take($targetUsers)->get();
        $this->refreshLegacySeedUserIdentities($participants);

        foreach ($participants as $user) {
            Profile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => 'Building in public with Laravel.',
                    'avatar_path' => 'https://picsum.photos/seed/avatar'.$user->id.'/200/200',
                    'cover_path' => 'https://picsum.photos/seed/cover'.$user->id.'/1200/400',
                    'location' => 'Remote',
                    'website' => 'https://example.com/'.$user->username,
                ]
            );
        }

        foreach ($participants as $user) {
            $targets = $participants
                ->where('id', '!=', $user->id)
                ->shuffle()
                ->take(random_int(3, 5));

            foreach ($targets as $target) {
                Follow::query()->firstOrCreate([
                    'follower_id' => $user->id,
                    'following_id' => $target->id,
                ]);
            }
        }

        $posts = collect();
        $postCounter = 0;

        foreach ($participants as $user) {
            for ($i = 0; $i < 3; $i++) {
                $post = Post::query()->create([
                    'user_id' => $user->id,
                    'content' => 'Seed post #'.($postCounter + 1).' by @'.$user->username,
                    'created_at' => now()->subMinutes($postCounter * 3),
                    'updated_at' => now()->subMinutes($postCounter * 3),
                ]);

                $this->attachVariedMedia($post, $postCounter);
                $posts->push($post);
                $postCounter++;
            }
        }

        $reactionTypes = ['like', 'love', 'laugh', 'wow', 'sad', 'angry'];
        $commentTemplates = [
            'This shot is amazing, love the mood.',
            'Great post, thanks for sharing this.',
            'The colors in this media are super clean.',
            'I would love to know where this was taken.',
            'This is exactly the kind of content I like.',
            'Really nice composition and framing.',
            'Awesome update, keep posting more like this.',
        ];

        $replyTemplates = [
            'Totally agree with you on this.',
            'Same here, this one is my favorite too.',
            'Good point, I noticed that as well.',
            'Thanks, that means a lot.',
            'I had the same thought when I saw it.',
        ];

        foreach ($posts as $post) {
            $commentAuthors = $participants->shuffle()->take(random_int(1, 3));
            $topComments = collect();

            foreach ($commentAuthors as $author) {
                $comment = Comment::query()->create([
                    'post_id' => $post->id,
                    'user_id' => $author->id,
                    'content' => $commentTemplates[array_rand($commentTemplates)],
                ]);
                $topComments->push($comment);

                if (random_int(0, 1) === 1) {
                    $replier = $participants->shuffle()->first();
                    Comment::query()->create([
                        'post_id' => $post->id,
                        'user_id' => $replier->id,
                        'parent_comment_id' => $comment->id,
                        'content' => $replyTemplates[array_rand($replyTemplates)],
                    ]);
                }
            }

            $postReactors = $participants->shuffle()->take(random_int(2, 6));
            foreach ($postReactors as $reactor) {
                Reaction::query()->firstOrCreate([
                    'user_id' => $reactor->id,
                    'reactable_type' => $post->getMorphClass(),
                    'reactable_id' => (string) $post->id,
                ], [
                    'type' => $reactionTypes[array_rand($reactionTypes)],
                ]);
            }

            $comments = Comment::query()->where('post_id', $post->id)->get();
            foreach ($comments as $comment) {
                $commentReactors = $participants->shuffle()->take(random_int(0, 3));
                foreach ($commentReactors as $reactor) {
                    Reaction::query()->firstOrCreate([
                        'user_id' => $reactor->id,
                        'reactable_type' => $comment->getMorphClass(),
                        'reactable_id' => (string) $comment->id,
                    ], [
                        'type' => $reactionTypes[array_rand($reactionTypes)],
                    ]);
                }
            }
        }
    }

    private function resetSocialTables(): void
    {
        DB::table('reactions')->delete();
        DB::table('comments')->delete();
        DB::table('post_media')->delete();
        DB::table('posts')->delete();
        DB::table('follows')->delete();
        DB::table('profiles')->delete();
    }

    private function createUsers(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            ['name' => $fullName, 'username' => $username] = $this->generateIdentity();

            User::query()->create([
                'name' => $fullName,
                'username' => $username,
                'email' => $username.'@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
    }

    private function refreshLegacySeedUserIdentities(iterable $participants): void
    {
        foreach ($participants as $participant) {
            if (! $participant instanceof User) {
                continue;
            }

            $isLegacySeedName = str_starts_with((string) $participant->name, 'Seed User ');
            $isLegacySeedUsername = (bool) preg_match('/^seed_\d+_\d+$/', (string) $participant->username);

            if (! $isLegacySeedName && ! $isLegacySeedUsername) {
                continue;
            }

            ['name' => $fullName, 'username' => $username] = $this->generateIdentity((string) $participant->id);

            $participant->forceFill([
                'name' => $fullName,
                'username' => $username,
                'email' => $username.'@example.com',
            ])->save();
        }
    }

    /**
     * @return array{name: string, username: string}
     */
    private function generateIdentity(?string $ignoreUserId = null): array
    {
        $firstNames = array_map(static fn (SeedFirstName $name): string => $name->value, SeedFirstName::cases());
        $lastNames = array_map(static fn (SeedLastName $name): string => $name->value, SeedLastName::cases());

        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $fullName = $firstName.' '.$lastName;

        $baseHandle = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $firstName.'_'.$lastName) ?? 'seed_user');
        $baseHandle = trim($baseHandle, '_');
        if ($baseHandle === '') {
            $baseHandle = 'seed_user';
        }

        $username = $baseHandle;
        $suffix = 2;

        while (
            User::query()
                ->when($ignoreUserId !== null, static fn ($query) => $query->where('id', '!=', $ignoreUserId))
                ->where('username', $username)
                ->exists()
        ) {
            $username = $baseHandle.'_'.$suffix;
            $suffix++;
        }

        return [
            'name' => $fullName,
            'username' => $username,
        ];
    }

    private function attachVariedMedia(Post $post, int $index): void
    {
        $mode = $index % 5;

        if ($mode === 0) {
            return;
        }

        if ($mode === 1) {
            PostMedia::query()->create([
                'post_id' => $post->id,
                'file_path' => 'https://picsum.photos/seed/post'.$index.'/800/600',
                'type' => 'image',
                'display_order' => 0,
                'alt_text' => 'single image',
            ]);

            return;
        }

        if ($mode === 2) {
            PostMedia::query()->create([
                'post_id' => $post->id,
                'file_path' => 'https://picsum.photos/seed/post'.$index.'a/800/600',
                'type' => 'image',
                'display_order' => 0,
                'alt_text' => 'gallery image one',
            ]);

            PostMedia::query()->create([
                'post_id' => $post->id,
                'file_path' => 'https://picsum.photos/seed/post'.$index.'b/800/600',
                'type' => 'image',
                'display_order' => 1,
                'alt_text' => 'gallery image two',
            ]);

            return;
        }

        if ($mode === 3) {
            PostMedia::query()->create([
                'post_id' => $post->id,
                'file_path' => 'https://samplelib.com/lib/preview/mp4/sample-5s.mp4',
                'type' => 'video',
                'display_order' => 0,
                'alt_text' => null,
            ]);

            return;
        }

        PostMedia::query()->create([
            'post_id' => $post->id,
            'file_path' => 'https://picsum.photos/seed/post'.$index.'mix/800/600',
            'type' => 'image',
            'display_order' => 0,
            'alt_text' => 'mixed post image',
        ]);

        PostMedia::query()->create([
            'post_id' => $post->id,
            'file_path' => 'https://samplelib.com/lib/preview/mp4/sample-10s.mp4',
            'type' => 'video',
            'display_order' => 1,
            'alt_text' => null,
        ]);
    }
}
