<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/PerspectiveCategoryController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Services\PerspectiveService;

class PerspectiveCategoryController extends Controller
{
    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}

    public function index(): Response
    {
        return Inertia::render('perspective-categories/index');
    }

    public function show(string $slug): Response
    {
        $category = PerspectiveCategory::where('slug', $slug)->firstOrFail();

        return Inertia::render('perspective-categories/show', [
            'category'     => $this->perspectiveService->transformPerspectiveCategory($category),
            'perspectives' => $this->perspectiveService->categoryPerspectives($category, request()->user()),
        ]);
    }
}
