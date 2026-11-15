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
        $rootId   = $perspective->rootId();

        return Inertia::render('thread', [
            'rootId'       => $rootId,
            'maxDepth'     => Perspective::MAX_DEPTH,
            'perspectives' => $this->perspectiveService->getPerspectiveThread($perspective, $request->user()),
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


}
