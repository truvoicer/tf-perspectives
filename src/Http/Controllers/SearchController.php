<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/SearchController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;
use Truvoicer\TfPerspectives\Http\Requests\SearchIndexRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Traits\Request\ValidatesFormRequests;

class SearchController extends Controller
{
    use ValidatesFormRequests;

    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}

    public function index(Request $request): Response
    {
        $request = $this->validateWithFormRequest($request, SearchIndexRequest::class, true);
        $q        = trim((string) $request->validated('q', ''));
        $tag      = trim((string) $request->validated('tag', ''));
        $mood     = (string) $request->validated('mood', '');
        $category = (string) $request->validated('category', '');
        $sort     = (string) $request->validated('sort', 'recent');

        return Inertia::render('search', [
            'perspectives' => $this->perspectiveService->search(request()->user(), [
                'q'        => $q,
                'tag'      => $tag,
                'mood'     => $mood,
                'category' => $category,
                'sort'     => $sort,
            ]),
            'popularTags'  => $this->perspectiveService->popularTags(),
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
