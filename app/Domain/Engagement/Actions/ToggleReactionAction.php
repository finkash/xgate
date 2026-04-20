<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ToggleReactionAction
{
    /**
     * Toggle reaction for a given user and reactable model, then return grouped counts.
     *
     * @return array<string, int>
     */
    public function execute(User $user, Model $reactable, ReactionType $type): array
    {
        DB::transaction(function () use ($user, $reactable, $type): void {
            $existing = Reaction::query()
                ->where('user_id', $user->id)
                ->where('reactable_type', $reactable->getMorphClass())
                ->where('reactable_id', (string) $reactable->getKey())
                ->first();

            if (! $existing) {
                $reaction = new Reaction([
                    'user_id' => $user->id,
                    'type' => $type,
                ]);

                $reaction->reactable()->associate($reactable);
                $reaction->save();

                return;
            }

            if ($existing->type === $type) {
                $existing->delete();

                return;
            }

            $existing->update(['type' => $type]);
        });

        $counts = Reaction::query()
            ->selectRaw('type, COUNT(*) as aggregate')
            ->where('reactable_type', $reactable->getMorphClass())
            ->where('reactable_id', (string) $reactable->getKey())
            ->groupBy('type')
            ->pluck('aggregate', 'type')
            ->map(fn ($count): int => (int) $count)
            ->all();

        $result = [];

        foreach (ReactionType::cases() as $reactionType) {
            $result[$reactionType->value] = $counts[$reactionType->value] ?? 0;
        }

        return $result;
    }
}
