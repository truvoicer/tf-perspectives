<?php
// app/Http/Controllers/PerspectiveController.php

namespace App\Http\Controllers;

use App\Http\Resources\PerspectiveResource;
use App\Models\Perspective;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PerspectiveController extends Controller
{
    /** The feed: perspectives that nobody has stepped into yet (roots). */
    public function index(Request $request)
    {
        $query = Perspective::query()
            ->whereNull('parent_id')
            ->with('author:id,name')
            ->withCount(['empathies', 'children'])
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $q->where(function (Builder $inner) use ($request) {
                    $inner->where('body', 'like', '%' . $request->string('q') . '%')
                          ->orWhere('voice', 'like', '%' . $request->string('q') . '%');
                });
            })
            ->latest('id');

        $this->applyViewerState($query);

        return PerspectiveResource::collection($query->paginate(12));
    }

    /**
     * One thread: the root perspective plus everything branched off it.
     * Returned flat — the client assembles the tree from parent_id.
     */
    public function show(Perspective $perspective)
    {
        $rootId = $perspective->rootId();

        $query = Perspective::query()
            ->thread($rootId)
            ->with('author:id,name')
            ->withCount(['empathies', 'children'])
            ->orderBy('depth')
            ->orderBy('id');

        $this->applyViewerState($query);

        return [
            'root_id' => $rootId,
            'max_depth' => Perspective::MAX_DEPTH,
            'data' => PerspectiveResource::collection($query->get()),
        ];
    }

    /** Start a new perspective, or step into someone else's shoes. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'body'      => ['required', 'string', 'min:10', 'max:2000'],
            'voice'     => ['nullable', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:perspectives,id'],
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
            'user_id'   => $request->user()->id,
            'parent_id' => $parent?->id,
            'root_id'   => $parent?->rootId(),
            'voice'     => $data['voice'] ?? null,
            'body'      => $data['body'],
            'depth'     => $parent ? $parent->depth + 1 : 0,
        ]);

        $perspective->load('author:id,name')->loadCount(['empathies', 'children']);

        return (new PerspectiveResource($perspective))
            ->response()
            ->setStatusCode(201);
    }

    /** Attach the current viewer's state (did they stand in these shoes?). */
    private function applyViewerState(Builder $query): void
    {
        $userId = auth('sanctum')->id();

        if ($userId) {
            $query->withExists([
                'empathies as has_empathized' => fn (Builder $q) => $q->where('user_id', $userId),
            ]);
        }
    }
}
