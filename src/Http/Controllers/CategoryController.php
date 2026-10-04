<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/CategoryController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\Category;
use Truvoicer\TfPerspectives\Models\Perspective;

class CategoryController extends Controller
{
    use TransformsPerspectives;

    public function index(): Response
    {
        $categories = Category::query()
            ->withCount('perspectives')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Category $c) => $this->transformCategory($c))
            ->all();

        return Inertia::render('@tf-perspectives::categories/index', [
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): Response
    {
        $viewerId = auth()->id();
        $category = Category::where('slug', $slug)->firstOrFail();

        $query = Perspective::query()
            ->whereNull('parent_id')
            ->where('category_id', $category->id)
            ->latest('id');

        $this->withCardRelations($query, $viewerId);

        $paginator = $query->paginate(12)->withQueryString()
            ->through(fn (Perspective $p) => $this->transform($p, $viewerId));

        return Inertia::render('@tf-perspectives::categories/show', [
            'category'     => $this->transformCategory($category),
            'perspectives' => $paginator,
        ]);
    }
}
