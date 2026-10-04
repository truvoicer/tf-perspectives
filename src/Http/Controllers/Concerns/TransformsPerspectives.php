<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/Concerns/TransformsPerspectives.php

namespace Truvoicer\TfPerspectives\Http\Controllers\Concerns;

use Truvoicer\TfPerspectives\Models\Perspective;

trait TransformsPerspectives
{
    /**
     * Shape a Perspective model into the array the React cards expect.
     * Call sites should eager-load: author, category, tags, reactions, and
     * withCount children + bookmarks, plus the is_bookmarked exists-flag.
     */
    protected function transform(Perspective $p, ?int $viewerId = null): array
    {
        $viewerId ??= auth()->id();

        // Anonymous posts hide the author unless you wrote it.
        $showAuthor = ! $p->is_anonymous || ($viewerId && $viewerId === $p->user_id);

        return [
            'id'             => $p->id,
            'parent_id'      => $p->parent_id,
            'root_id'        => $p->root_id,
            'depth'          => (int) $p->depth,
            'voice'          => $p->voice,
            'body'           => $p->body,
            'mood'           => $p->mood,
            'is_anonymous'   => (bool) $p->is_anonymous,
            'author'         => $showAuthor && $p->relationLoaded('author') && $p->author
                ? $this->transformAuthor($p->author)
                : null,
            'category'       => $p->relationLoaded('category') && $p->category
                ? $this->transformCategory($p->category)
                : null,
            'tags'           => $p->relationLoaded('tags')
                ? $p->tags->pluck('name')->values()->all()
                : [],
            'reactions'      => $this->transformReactions($p, $viewerId),
            'children_count' => (int) ($p->children_count ?? 0),
            'bookmark_count' => (int) ($p->bookmarks_count ?? 0),
            'is_bookmarked'  => (bool) ($p->is_bookmarked ?? false),
            'can_branch'     => $p->depth < Perspective::MAX_DEPTH,
            'created_at'     => optional($p->created_at)->toIso8601String(),
        ];
    }

    protected function transformAuthor($user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'handle'     => $user->handle ?? 'user-'.$user->id,
            'avatar_url' => $user->avatar_url ?? null,
        ];
    }

    protected function transformCategory($category): array
    {
        return [
            'id'          => $category->id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $category->description,
            'icon'        => $category->icon,
            'color'       => $category->color,
            'perspectives_count' => isset($category->perspectives_count)
                ? (int) $category->perspectives_count
                : null,
        ];
    }

    /**
     * @return array{counts: array<string,int>, mine: string|null}
     */
    protected function transformReactions(Perspective $p, ?int $viewerId): array
    {
        $counts = [
            'empathy' => 0,
            'insight' => 0,
            'relate'  => 0,
            'curious' => 0,
        ];

        $mine = null;

        if ($p->relationLoaded('reactions')) {
            foreach ($p->reactions as $reaction) {
                if (array_key_exists($reaction->type, $counts)) {
                    $counts[$reaction->type]++;
                }
                if ($viewerId && $reaction->user_id === $viewerId) {
                    $mine = $reaction->type;
                }
            }
        }

        return ['counts' => $counts, 'mine' => $mine];
    }

    /**
     * Reusable eager-loading bundle for any query that will be transformed.
     */
    protected function withCardRelations($query, ?int $viewerId): void
    {
        $query
            ->with([
                'author:id,name,handle,avatar_url',
                'category:id,name,slug,description,icon,color',
                'tags:id,name',
                'reactions:id,perspective_id,user_id,type',
            ])
            ->withCount(['children', 'bookmarks']);

        if ($viewerId) {
            $query->withExists([
                'bookmarks as is_bookmarked' => fn ($q) => $q->where('user_id', $viewerId),
            ]);
        }
    }

    /**
     * Persist a comma-separated tag string onto a perspective.
     */
    protected function syncTags(Perspective $perspective, ?string $raw): void
    {
        if ($raw === null || $raw === '') {
            $perspective->tags()->sync([]);
            return;
        }

        $names = collect(explode(',', $raw))
            ->map(fn ($t) => trim(strtolower($t)))
            ->filter(fn ($t) => $t !== '' && preg_match('/^[a-z0-9-]+$/', $t))
            ->unique()
            ->take(5)
            ->values();

        $ids = $names->map(function (string $name) {
            return \Truvoicer\TfPerspectives\Models\Tag::firstOrCreate(
                ['name' => $name],
            )->id;
        });

        $perspective->tags()->sync($ids->all());
    }
}
