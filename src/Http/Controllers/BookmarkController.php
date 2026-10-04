<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/BookmarkController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\Bookmark;
use Truvoicer\TfPerspectives\Models\Perspective;

class BookmarkController extends Controller
{
    use TransformsPerspectives;

    public function index()
    {
        $viewerId = auth()->id();

        $query = Perspective::query()
            ->whereIn('id', function ($sub) use ($viewerId) {
                $sub->select('perspective_id')
                    ->from('bookmarks')
                    ->where('user_id', $viewerId);
            })
            ->latest('id');

        $this->withCardRelations($query, $viewerId);

        $paginator = $query->paginate(12)->withQueryString()
            ->through(fn (Perspective $p) => $this->transform($p, $viewerId));

        return Inertia::render('@tf-perspectives::bookmarks', [
            'perspectives' => $paginator,
        ]);
    }

    public function toggle(\Illuminate\Http\Request $request, Perspective $perspective)
    {
        $userId = $request->user()->id;

        $existing = Bookmark::where('perspective_id', $perspective->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            Bookmark::create([
                'perspective_id' => $perspective->id,
                'user_id'        => $userId,
            ]);
        }

        return redirect()->back();
    }
}
