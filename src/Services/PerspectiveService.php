<?php

namespace Truvoicer\TfPerspectives\Services;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsShoes;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Models\ShoeDiscussion;
use Truvoicer\TfPerspectives\Models\Tag;
use Truvoicer\TfPerspectives\Models\User;

class PerspectiveService
{

    use TransformsPerspectives;
    use TransformsShoes;

    private bool $paginate = true;
    private int $perPage = 12;
    private ?int $limit = null;
    private int $statsStep = 1;
    private ?int $page = null;

    public function setPage(int $page): self
    {
        $this->page = max(1, $page);
        return $this;
    }

    public function setStatsStep(int $step): self
    {
        $this->statsStep = max(1, $step);
        return $this;
    }

    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function setPaginate(bool $paginate): self
    {
        $this->paginate = $paginate;
        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function fetchPerspectives(?User $user, string $search = '', string $tab = 'default')
    {
        $viewerId = $user?->id;
        $query = $this->fetchPerspectivesQuery($user, $search, $tab);

        if ($this->paginate) {
            $result = $this->page
                ? $query->paginate($this->perPage, ['*'], 'page', $this->page)->withQueryString()
                : $query->paginate($this->perPage)->withQueryString();
        } else {
            $result = $this->limit
                ? $query->limit($this->limit)->get()
                : $query->get();
        }
        return $result->through(fn(Perspective $p) => $this->transform($p, $viewerId));
    }

    public function fetchPerspectivesQuery(?User $user, string $search = '', string $tab = 'default')
    {
        $viewerId = $user?->id;
        $query = Perspective::query()
            ->whereNull('parent_id')
            ->when($search !== '', function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('body', 'like', "%{$search}%")
                        ->orWhere('voice', 'like', "%{$search}%");
                });
            });

        match ($tab) {
            'trending'  => $query->withCount('reactions')->orderByDesc('reactions_count')
                ->orderByDesc('id'),
            'featured'  => $query->where('is_featured', true)->latest('id'),
            'following' => $viewerId
                ? $query->whereIn('user_id', function ($sub) use ($viewerId) {
                    $sub->select('followed_id')
                        ->from('follows')
                        ->where('follower_id', $viewerId);
                })->latest('id')
                : $query->whereRaw('1 = 0'),
            default     => $query->latest('id'),
        };

        $this->withCardRelations($query, $viewerId);
        return $query;
    }

    /**
     * Returns a paginator (or a plain collection if paginate is false).
     */
    public function getFeaturedPerspectives(?User $user)
    {
        $viewerId = $user?->id;
        $query = $this->getFeaturedQuery($user);

        if ($this->paginate) {
            $result = $this->page
                ? $query->paginate($this->perPage, ['*'], 'page', $this->page)->withQueryString()
                : $query->paginate($this->perPage)->withQueryString();
        } else {
            $result = $this->limit
                ? $query->limit($this->limit)->get()
                : $query->get();
        }


        return $result->through(fn(Perspective $p) => $this->transform($p, $viewerId));
    }

    /**
     * Returns the base query (unexecuted) for featured perspectives.
     */
    public function getFeaturedQuery(?User $user): Builder
    {
        $viewerId = $user?->id;

        $query = Perspective::query()
            ->whereNull('parent_id')
            ->where('is_featured', true)
            ->when($viewerId, fn($q) => $q->where('user_id', '!=', $viewerId))
            ->withCount('reactions')
            ->orderByDesc('reactions_count')
            ->orderByDesc('id'); // secondary sort for stability

        $this->withCardRelations($query, $viewerId);

        return $query;
    }
    /**
     * @return LengthAwarePaginator
     */
    public function getPerspectiveStats(?array $filters = null): LengthAwarePaginator
    {
        // 1. Build the flat array of stat objects
        $allStats = [];

        if (! $filters || array_key_exists('total_perspectives', $filters)) {
            $allStats[] = [
                'key'   => 'total_perspectives',
                'value' => Perspective::count(),
                'affix' => 'perspectives',
            ];
        }
        if (! $filters || array_key_exists('total_branches', $filters)) {
            $allStats[] = [
                'key'   => 'total_branches',
                'value' => Perspective::whereNotNull('parent_id')->count(),
                'affix' => 'branches',
            ];
        }
        if (! $filters || array_key_exists('total_voices', $filters)) {
            $allStats[] = [
                'key'   => 'total_voices',
                'value' => Perspective::distinct('user_id')->count('user_id'),
                'affix' => 'voices',
            ];
        }
        if (! $filters || array_key_exists('today', $filters)) {
            $allStats[] = [
                'key'   => 'today',
                'value' => Perspective::where('created_at', '>=', now()->startOfDay())->count(),
                'affix' => 'today',
            ];
        }

        // 2. Group the stats into chunks of $this->statsStep
        $groupedStats = array_chunk($allStats, $this->statsStep);

        // 3. Paginate the grouped stats manually
        $page = $this->page ?? 1;
        $items = array_slice($groupedStats, ($page - 1) * $this->perPage, $this->perPage);

        return new LengthAwarePaginator(
            $items,
            count($groupedStats),
            $this->perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    public function getPerspectiveThread(Perspective $perspective, ?User $user = null, ?array $filters = []): array
    {
        $viewerId = $user?->id;
        $rootId   = $perspective->rootId();

        // Record a view of the root perspective the user opened.
        \Truvoicer\TfPerspectives\Models\PerspectiveView::record(
            $perspective,
            $user,
        );

        // ── 1. Fetch the thread (root + branches) ────────────────────────
        $query = Perspective::query()
            ->where(function (Builder $q) use ($rootId) {
                $q->where('id', $rootId)->orWhere('root_id', $rootId);
            })
            ->orderBy('depth')
            ->orderBy('id');

        $this->withCardRelations($query, $viewerId);

        // ── 2. Eager-load shoes for the root perspective only ────────────
        //    We don't load shoes on every branch — a branch is already a
        //    view, and shoes attach to the perspective (the root).
        $query->with([
            'shoes' => function ($q) use ($viewerId) {
                // Ranked order: view-changers first, then most-reacted.
                $q->with('author:id,first_name,last_name,display_name')
                    ->ranked()
                    ->with([
                        'discussions' => function ($dq) {
                            $dq->with('author:id,first_name,last_name,display_name')
                                ->oldest('id');
                        },
                    ]);
            },
        ]);

        // Only attach the shoes relation to the root. Branches shouldn't
        // carry the root's shoes.
        $query->where(function (Builder $q) use ($rootId) {
            $q->where('id', $rootId)     // root gets shoes eager-loaded
                ->orWhere('root_id', $rootId);
        });

        $perspectives = $query->get()
            ->map(function (Perspective $p) use ($viewerId, $rootId) {
                $transformed = $this->transform($p, $viewerId);

                // Only the root perspective carries its shoes.
                if ($p->id === $rootId && $p->relationLoaded('shoes')) {
                    $transformed['shoes'] = $p->shoes
                        ->map(fn($shoe) => $this->transformShoeWithDiscussions($shoe, $viewerId))
                        ->values()
                        ->all();
                    $transformed['shoes_count'] = $p->shoes->count();
                } else {
                    $transformed['shoes'] = [];
                    $transformed['shoes_count'] = 0;
                }

                return $transformed;
            })
            ->all();

        return $perspectives;
    }

    /**
     * Transform a shoe along with its discussion tree.
     *
     * The discussions come back from the DB flat, ordered by id. We rebuild
     * them into a nested tree here so the React side can just render
     * `discussion.children` without any tree-building logic.
     */
    private function transformShoeWithDiscussions($shoe, ?int $viewerId): array
    {
        $transformed = $this->transformShoe($shoe, $viewerId);

        $discussions = $shoe->relationLoaded('discussions')
            ? $shoe->discussions
            : collect();

        $transformed['discussions'] = $this->buildDiscussionTree($discussions, $viewerId);

        return $transformed;
    }

    /**
     * Build a nested discussion tree from a flat collection.
     *
     * Returns an array of root-level discussions (parent_id === null), each
     * with a `children` array populated recursively.
     *
     * @param  \Illuminate\Support\Collection<int, ShoeDiscussion>  $discussions
     * @return array<int, array>
     */
    private function buildDiscussionTree($discussions, ?int $viewerId): array
    {
        // First pass: transform every node and stash it by id.
        $nodes = [];
        foreach ($discussions as $discussion) {
            $nodes[$discussion->id] = array_merge(
                $this->transformDiscussion($discussion, $viewerId),
                ['children' => []],
            );
        }

        // Second pass: hang children off parents.
        $tree = [];
        foreach ($nodes as $id => &$node) {
            if ($node['parent_id'] !== null && isset($nodes[$node['parent_id']])) {
                $nodes[$node['parent_id']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);

        return $tree;
    }


    public function search(?User $user, ?array $filters = [])
    {
        $viewerId = $user?->id;

        $query = $this->searchQuery($user, $filters);
        if ($this->paginate) {
            $result = $this->page
                ? $query->paginate($this->perPage, ['*'], 'page', $this->page)
                : $query->paginate($this->perPage);
        } else {
            $result = $this->limit
                ? $query->limit($this->limit)->get()
                : $query->get();
        }

        return $result->through(fn (Perspective $p) => $this->transform($p, $viewerId));
    }

    public function searchQuery(?User $user, ?array $filters = [])
    {
        $viewerId = $user?->id;

        $q        = trim((string) ($filters['q'] ?? ''));
        $tag      = trim((string) ($filters['tag'] ?? ''));
        $mood     = (string) ($filters['mood'] ?? '');
        $category = (string) ($filters['category'] ?? '');
        $sort     = (string) ($filters['sort'] ?? 'recent');

        $query = Perspective::query()->whereNull('parent_id');

        if ($q !== '') {
            $query->where(function (Builder $inner) use ($q) {
                $inner->where('body', 'like', "%{$q}%")
                      ->orWhere('voice', 'like', "%{$q}%")
                      ->orWhereHas('author', fn (Builder $a) => $a->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('tags', fn (Builder $t) => $t->where('name', 'like', "%{$q}%"));
            });
        }

        if ($tag !== '') {
            $query->whereHas('tags', fn (Builder $t) => $t->where('name', $tag));
        }

        if ($mood !== '') {
            $query->where('mood', $mood);
        }

        if ($category !== '') {
            $query->whereHas('category', fn (Builder $c) => $c->where('slug', $category));
        }

        match ($sort) {
            'popular'  => $query->withCount('reactions')->orderByDesc('reactions_count')->orderByDesc('id'),
            'branched' => $query->withCount('children')->orderByDesc('children_count')->orderByDesc('id'),
            default    => $query->latest('id'),
        };

        $this->withCardRelations($query, $viewerId);
        return $query;

    }

    public function popularTags()
    {
        $query = $this->popularTagsQuery();
        if ($this->paginate) {
            $result = $this->page
                ? $query->paginate($this->perPage, ['*'], 'page', $this->page)
                : $query->paginate($this->perPage);
        } else {
            $result = $this->limit
                ? $query->limit($this->limit)->get()
                : $query->get();
        }

            return $result;
    }

    public function popularTagsQuery()
    {
        return Tag::query()
            ->withCount('perspectives')
            ->orderByDesc('perspectives_count')
            ->limit(12);
    }
}
