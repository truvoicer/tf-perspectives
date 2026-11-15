<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/PerspectiveController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsShoes;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\ShoeDiscussion;
use Truvoicer\TfPerspectives\Services\CategoryService;
use Truvoicer\TfPerspectives\Services\PerspectiveService;

class PerspectiveController extends Controller
{
    use TransformsPerspectives;
    use TransformsShoes;

    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}


    public function index(Request $request): Response
    {
        $search = $request->input('q', '');
        $tab = $request->input('tab', 'default');

        return Inertia::render('feed', [
            'perspectives' => $this->perspectiveService->setPaginate(true)->fetchPerspectives(
                $request->user(),
                $search ?? '',
                $tab ?? 'default'
            ),
            'filters'      => ['q' => $search, 'tab' => $tab],
            'featured'     => $this->perspectiveService->setPaginate(true)->setLimit(5)->getFeaturedPerspectives($request->user()),
            'stats'        => $this->perspectiveService->getPerspectiveStats(),
        ]);
    }

    public function show(Request $request, Perspective $perspective): Response
    {
        $viewerId = $request->user()?->id;
        $rootId   = $perspective->rootId();

        // Record a view of the root perspective the user opened.
        \Truvoicer\TfPerspectives\Models\PerspectiveView::record(
            $perspective,
            $request->user(),
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

        return Inertia::render('thread', [
            'rootId'       => $rootId,
            'maxDepth'     => Perspective::MAX_DEPTH,
            'perspectives' => $perspectives,
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'body'         => ['required', 'string', 'min:10', 'max:2000'],
            'voice'        => ['nullable', 'string', 'max:120'],
            'parent_id'    => ['nullable', 'integer', 'exists:perspectives,id'],
            'perspective_category_id'  => ['nullable', 'integer', 'exists:perspective_categories,id'],
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
            'perspective_category_id'  => $parent?->perspective_category_id ?? ($data['perspective_category_id'] ?? null),
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

    // packages/truvoicer/tf-perspectives/src/Http/Controllers/PerspectiveController.php

    public function update(Request $request, Perspective $perspective)
    {
        abort_unless(
            $request->user() && $request->user()->id === $perspective->user_id,
            403,
            'You can only edit your own perspectives.',
        );

        $data = $request->validate([
            'body'                    => ['required', 'string', 'min:10', 'max:2000'],
            'voice'                   => ['nullable', 'string', 'max:120'],
            'perspective_category_id' => ['nullable', 'integer', 'exists:perspective_categories,id'],
            'mood'                    => ['nullable', 'string', 'max:32'],
            'tags'                    => ['nullable', 'string', 'max:255'],
            'is_anonymous'            => ['nullable', 'boolean'],
        ]);

        $perspective->fill([
            'voice'                   => $data['voice'] ?? null,
            'body'                    => $data['body'],
            'mood'                    => $data['mood'] ?? null,
            'is_anonymous'            => (bool) ($data['is_anonymous'] ?? false),
            // Only allow changing the category on a root perspective. Branch
            // categories are inherited from their parent, so editing them here
            // would create a mismatch in the tree.
            'perspective_category_id' => $perspective->parent_id === null
                ? ($data['perspective_category_id'] ?? null)
                : $perspective->perspective_category_id,
        ]);

        $perspective->save();

        $this->syncTags($perspective, $data['tags'] ?? null);

        return redirect()->back();
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
}
