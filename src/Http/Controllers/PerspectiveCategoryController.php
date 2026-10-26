<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/PerspectiveCategoryController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Services\CategoryService;

class PerspectiveCategoryController extends Controller
{

    use TransformsPerspectives;

    public function index(): Response
    {
        return Inertia::render('perspective-categories/index');
    }

    public function show(string $slug): Response
    {
        $viewerId = request()->user()?->id;
        $category = PerspectiveCategory::where('slug', $slug)->firstOrFail();

        $query = Perspective::query()
            ->whereNull('parent_id')
            ->where('perspective_category_id', $category->id)
            ->latest('id');

        $this->withCardRelations($query, $viewerId);

        $paginator = $query->paginate(12)->withQueryString()
            ->through(fn (Perspective $p) => $this->transform($p, $viewerId));

        return Inertia::render('perspective-categories/show', [
            'category'     => $this->transformPerspectiveCategory($category),
            'perspectives' => $paginator,
        ]);
    }
}
