<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Http\Requests\SearchIndexRequest;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Contracts\Override;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;

class PerspectivesSearchBlock extends PerspectivesBlock
{

    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}

    private function getFilters(): array
    {
        $validated = $this->getValidator()?->validated();
        return [
            'q'        => (!empty($validated['q'])) ? trim((string) $validated['q'] ?? '') : '',
            'tag'      => (!empty($validated['tag'])) ? trim((string) $validated['tag'] ?? '') : '',
            'mood'     => (!empty($validated['mood'])) ? trim((string) $validated['mood'] ?? '') : '',
            'category' => (!empty($validated['category'])) ? trim((string) $validated['category'] ?? '') : '',
            'sort'     => (!empty($validated['sort'])) ? trim((string) $validated['sort'] ?? '') : 'recent',
        ];
    }

    public function inertiaDependencies(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $filters = $this->getFilters();
        $data = $block->getData();
        return [
            'user' => Inertia::optional(
                function () {
                    $user = $this->getUser();

                    return $user ? $this->userResource::make($user) : null;
                }
            ),
            'filters' => $filters,
            'perspectives' => $this->perspectiveService->search(request()->user(), [
                'q'        => $filters['q'],
                'tag'      => $filters['tag'],
                'mood'     => $filters['mood'],
                'category' => $filters['category'],
                'sort'     => $filters['sort'],
            ]),
            'popularTags'  => $this->perspectiveService->popularTags(),
            ...$this->buildPermalinks($data),
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();
        $data = $block->getData();

        $filters = $this->getFilters();
        return [
            'user' => $user ? $this->userResource::make($user) : null,
            'filters' => $filters,
            'perspectives' => $this->perspectiveService->search(request()->user(), [
                'q'        => $filters['q'],
                'tag'      => $filters['tag'],
                'mood'     => $filters['mood'],
                'category' => $filters['category'],
                'sort'     => $filters['sort'],
            ]),
            'popularTags'  => $this->perspectiveService->popularTags(),
            ...$this->buildPermalinks($data),
        ];
    }

    public function formRequestRules(
        PageColumnBlock $block,
    ): array {
        return app(Override::resolveClass(SearchIndexRequest::class))->rules();
    }

    public function parseData(
        PageColumnBlock $block,
    ): array {
        $data = parent::parseData($block);
        $pageTypes = [
            'thread_page',
            'feed_page',
            'categories_page',
            'category_thread_page',
            'search_page',
            'search_results_page',
        ];

        return $this->parsePageTypes($data, $pageTypes);
    }
}
