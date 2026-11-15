<?php

namespace Truvoicer\TfPerspectives\Services;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Models\User;

class PerspectiveService
{

    use TransformsPerspectives;

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
}
