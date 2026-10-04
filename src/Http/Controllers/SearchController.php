<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/SearchController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\Category;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\Tag;

class SearchController extends Controller
{
    use TransformsPerspectives;

    public function index(Request $request): Response
    {
        $viewerId = auth()->id();

        $q        = trim((string) $request->input('q', ''));
        $tag      = trim((string) $request->input('tag', ''));
        $mood     = (string) $request->input('mood', '');
        $category = (string) $request->input('category', '');
        $sort     = (string) $request->input('sort', 'recent');

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

        $paginator = $query->paginate(12)->withQueryString()
            ->through(fn (Perspective $p) => $this->transform($p, $viewerId));

        return Inertia::render('@tf-perspectives::search', [
            'perspectives' => $paginator,
            'categories'   => Category::orderBy('sort_order')
                ->get()
                ->map(fn (Category $c) => $this->transformCategory($c))
                ->all(),
            'popularTags'  => Tag::query()
                ->withCount('perspectives')
                ->orderByDesc('perspectives_count')
                ->limit(12)
                ->pluck('name')
                ->all(),
            'filters'      => [
                'q'        => $q,
                'tag'      => $tag,
                'mood'     => $mood,
                'category' => $category,
                'sort'     => $sort,
            ],
        ]);
    }
}
