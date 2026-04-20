<?php

namespace Tests\Feature;

use App\Domain\IdentityAndAccess\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_follow_another_user(): void
    {
        $follower = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($follower)
            ->postJson(route('users.follow', $target));

        $response->assertOk()
            ->assertJson([
                'following' => true,
                'created' => true,
            ]);

        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'following_id' => $target->id,
        ]);
    }

    public function test_follow_is_unique_for_same_user_pair(): void
    {
        $follower = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($follower)->postJson(route('users.follow', $target))->assertOk();
        $this->actingAs($follower)->postJson(route('users.follow', $target))->assertOk()
            ->assertJson([
                'following' => true,
                'created' => false,
            ]);

        $count = Follow::query()
            ->where('follower_id', $follower->id)
            ->where('following_id', $target->id)
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('users.follow', $user));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user']);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $user->id,
            'following_id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_unfollow_user(): void
    {
        $follower = User::factory()->create();
        $target = User::factory()->create();

        Follow::query()->create([
            'follower_id' => $follower->id,
            'following_id' => $target->id,
        ]);

        $response = $this->actingAs($follower)
            ->deleteJson(route('users.unfollow', $target));

        $response->assertOk()
            ->assertJson([
                'following' => false,
                'deleted' => true,
            ]);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $follower->id,
            'following_id' => $target->id,
        ]);
    }
}
