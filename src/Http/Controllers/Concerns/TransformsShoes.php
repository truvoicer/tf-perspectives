<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/Concerns/TransformsShoes.php

namespace Truvoicer\TfPerspectives\Http\Controllers\Concerns;

use Truvoicer\TfPerspectives\Models\PerspectiveShoe;
use Truvoicer\TfPerspectives\Models\ShoeDiscussion;

trait TransformsShoes
{
    protected function transformShoe(PerspectiveShoe $shoe, ?int $viewerId = null): array
    {
        $viewerId ??= request()->user()?->id;
        $showAuthor = ! $shoe->is_anonymous || ($viewerId && $viewerId === $shoe->user_id);

        return [
            'id'                => $shoe->id,
            'perspective_id'    => $shoe->perspective_id,
            'voice'             => $shoe->voice,
            'body'              => $shoe->body,
            'mood'              => $shoe->mood,
            'is_anonymous'      => (bool) $shoe->is_anonymous,
            'author'            => $showAuthor && $shoe->relationLoaded('author') && $shoe->author
                ? $this->transformShoeAuthor($shoe->author)
                : null,
            'is_owner'          => $viewerId !== null && $viewerId === $shoe->user_id,
            'discussions_count' => (int) $shoe->discussions_count,
            'empathies_count'   => (int) $shoe->empathies_count,
            'impact_rank'       => $shoe->impact_rank,
            'changed_my_view'   => (bool) $shoe->changed_my_view,
            'created_at'        => optional($shoe->created_at)->toIso8601String(),
        ];
    }

    protected function transformShoeAuthor($user): array
    {
        return [
            'id'           => $user->id,
            'first_name'   => $user->first_name,
            'last_name'    => $user->last_name,
            'display_name' => $user->display_name ?? 'user-'.$user->id,
        ];
    }

    protected function transformDiscussion(ShoeDiscussion $discussion, ?int $viewerId = null): array
    {
        $viewerId ??= request()->user()?->id;
        $showAuthor = ! $discussion->is_anonymous || ($viewerId && $viewerId === $discussion->user_id);

        return [
            'id'           => $discussion->id,
            'shoe_id'      => $discussion->shoe_id,
            'parent_id'    => $discussion->parent_id,
            'depth'        => (int) $discussion->depth,
            'body'         => $discussion->body,
            'is_anonymous' => (bool) $discussion->is_anonymous,
            'is_owner'     => $viewerId !== null && $viewerId === $discussion->user_id,
            'author'       => $showAuthor && $discussion->relationLoaded('author') && $discussion->author
                ? $this->transformShoeAuthor($discussion->author)
                : null,
            'created_at'   => optional($discussion->created_at)->toIso8601String(),
        ];
    }

    /**
     * Eager-load bundle for a query that will be transformed.
     */
    protected function withShoeRelations($query, ?int $viewerId): void
    {
        $query->with(['author:id,first_name,last_name,display_name']);
    }

    protected function withDiscussionRelations($query): void
    {
        $query->with(['author:id,first_name,last_name,display_name']);
    }
}
