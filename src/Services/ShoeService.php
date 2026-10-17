<?php
// packages/truvoicer/tf-perspectives/src/Services/ShoeService.php

namespace Truvoicer\TfPerspectives\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveShoe;
use Truvoicer\TfPerspectives\Models\ShoeDiscussion;

class ShoeService
{
    /**
     * Add a shoe to a perspective.
     *
     * Rules:
     *   - The perspective must be a root (parent_id === null). Shoes don't
     *     attach to branches — a branch is already someone else's view.
     *   - The voice is required; a shoe without "whose shoes" isn't a shoe.
     */
    public function addShoe(
        Perspective $perspective,
        int $userId,
        string $voice,
        string $body,
        ?string $mood = null,
        bool $anonymous = false,
    ): PerspectiveShoe {
        if ($perspective->parent_id !== null) {
            throw ValidationException::withMessages([
                'perspective_id' => 'Shoes attach to a perspective, not to another view.',
            ]);
        }

        return DB::transaction(function () use (
            $perspective, $userId, $voice, $body, $mood, $anonymous,
        ) {
            return PerspectiveShoe::create([
                'perspective_id' => $perspective->id,
                'user_id'        => $userId,
                'voice'          => $voice,
                'body'           => $body,
                'mood'           => $mood,
                'is_anonymous'   => $anonymous,
            ]);
        });
    }

    /**
     * Post a discussion on a shoe, or a reply to an existing discussion.
     */
    public function addDiscussion(
        PerspectiveShoe $shoe,
        int $userId,
        string $body,
        ?int $parentId = null,
        bool $anonymous = false,
    ): ShoeDiscussion {
        $parent = null;

        if ($parentId !== null) {
            $parent = ShoeDiscussion::findOrFail($parentId);

            if ($parent->shoe_id !== $shoe->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'That discussion belongs to a different shoe.',
                ]);
            }

            if ($parent->depth >= ShoeDiscussion::MAX_DISCUSSION_DEPTH) {
                throw ValidationException::withMessages([
                    'parent_id' => 'This thread is as deep as it goes. Reply to the shoe itself.',
                ]);
            }
        }

        return DB::transaction(function () use (
            $shoe, $userId, $body, $parent, $anonymous,
        ) {
            return ShoeDiscussion::create([
                'shoe_id'      => $shoe->id,
                'user_id'      => $userId,
                'parent_id'    => $parent?->id,
                'body'         => $body,
                'is_anonymous' => $anonymous,
                'depth'        => $parent ? $parent->depth + 1 : 0,
            ]);
        });
    }

    /**
     * Remove a shoe and everything under it. Soft-deletes the shoe, and
     * cascade soft-deletes its discussions.
     */
    public function removeShoe(PerspectiveShoe $shoe): void
    {
        DB::transaction(function () use ($shoe) {
            $shoe->discussions()->delete();   // soft deletes
            $shoe->delete();
        });
    }

    /**
     * Reserved for the ranking feature. Not exposed from a controller yet.
     */
    public function setImpact(PerspectiveShoe $shoe, ?int $rank, bool $changedView): PerspectiveShoe
    {
        $shoe->update([
            'impact_rank'          => $rank,
            'changed_my_view'      => $changedView,
            'marked_by_author_at'  => now(),
        ]);

        return $shoe;
    }
}
