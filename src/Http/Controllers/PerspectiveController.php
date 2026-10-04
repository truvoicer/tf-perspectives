<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/PerspectiveController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\Category;
use Truvoicer\TfPerspectives\Models\Perspective;

class PerspectiveController extends Controller
{
    use TransformsPerspectives;

    public function index(Request $request): Response
    {
        $viewerId = auth()->id();
        $tab      = (string) $request->input('tab', 'latest');
        $search   = (string) $request->input('q', '');

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

        $paginator = $query->paginate(12)->withQueryString()
            ->through(fn (Perspective $p) => $this->transform($p, $viewerId));

        $featured = Perspective::query()
            ->whereNull('parent_id')
            ->where('is_featured', true)
            ->when($viewerId, fn ($q) => $q->where('user_id', '!=', $viewerId))
            ->withCount('reactions')
            ->orderByDesc('reactions_count')
            ->limit(3)
            ->get();

        $featuredQuery = Perspective::query()->whereIn('id', $featured->pluck('id'));
        $this->withCardRelations($featuredQuery, $viewerId);
        $featuredHydrated = $featuredQuery->get()->keyBy('id');
        $featuredOrdered  = $featured->pluck('id')
            ->map(fn ($id) => $featuredHydrated->get($id))
            ->filter()
            ->map(fn (Perspective $p) => $this->transform($p, $viewerId))
            ->values()
            ->all();

        return Inertia::render('@tf-perspectives::feed', [
            'perspectives' => $paginator,
            'featured'     => $featuredOrdered,
            'categories'   => Category::query()
                ->withCount('perspectives')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Category $c) => $this->transformCategory($c))
                ->all(),
            'filters'      => ['q' => $search, 'tab' => $tab],
            'stats'        => [
                'total_perspectives' => Perspective::count(),
                'total_branches'     => Perspective::whereNotNull('parent_id')->count(),
                'total_voices'       => Perspective::distinct('user_id')->count('user_id'),
                'today'              => Perspective::where('created_at', '>=', now()->startOfDay())->count(),
            ],
        ]);
    }

    public function show(Request $request, Perspective $perspective): Response
    {
        $viewerId = auth()->id();
        $rootId   = $perspective->rootId();

        $query = Perspective::query()
            ->where(function (Builder $q) use ($rootId) {
                $q->where('id', $rootId)->orWhere('root_id', $rootId);
            })
            ->orderBy('depth')
            ->orderBy('id');

        $this->withCardRelations($query, $viewerId);

        $perspectives = $query->get()
            ->map(fn (Perspective $p) => $this->transform($p, $viewerId))
            ->all();

        return Inertia::render('@tf-perspectives::thread', [
            'rootId'       => $rootId,
            'maxDepth'     => Perspective::MAX_DEPTH,
            'perspectives' => $perspectives,
            'categories'   => Category::orderBy('sort_order')
                ->get()
                ->map(fn (Category $c) => $this->transformCategory($c))
                ->all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'body'         => ['required', 'string', 'min:10', 'max:2000'],
            'voice'        => ['nullable', 'string', 'max:120'],
            'parent_id'    => ['nullable', 'integer', 'exists:perspectives,id'],
            'category_id'  => ['nullable', 'integer', 'exists:categories,id'],
            'mood'         => ['nullable', 'string', 'max:32'],
            'tags'         => ['nullable', 'string', 'max:255'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $parent = null;

        if (! empty($data['parent_id'])) {
            $parent = Perspective::findOrFail($data['parent_id']);

            if ($parent->depth >= Perspective::MAX_DEPTH) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Every shoe has a bottom. This branch is as deep as it goes.',
                ]);
            }
        }

        $perspective = Perspective::create([
            'user_id'      => $request->user()->id,
            'parent_id'    => $parent?->id,
            'root_id'      => $parent?->rootId(),
            'category_id'  => $parent?->category_id ?? ($data['category_id'] ?? null),
            'voice'        => $data['voice'] ?? null,
            'body'         => $data['body'],
            'mood'         => $data['mood'] ?? null,
            'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
            'depth'        => $parent ? $parent->depth + 1 : 0,
        ]);

        $this->syncTags($perspective, $data['tags'] ?? null);

        // Notify the parent author about the branch, unless it's their own.
        if ($parent && $parent->user_id !== $request->user()->id) {
            $parent->author?->notify(
                new \Truvoicer\TfPerspectives\Notifications\PerspectiveBranched(
                    $perspective,
                    $request->user(),
                ),
            );
        }

        return redirect()->back();
    }
}
