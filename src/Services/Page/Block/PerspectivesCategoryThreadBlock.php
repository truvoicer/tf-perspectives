<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Contracts\Override;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;

class PerspectivesCategoryThreadBlock extends PerspectivesBlock
{
    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}

    private function getPerspectiveCategory(): ?PerspectiveCategory
    {
        $parameters = $this->getParameters();
        $category = $parameters['perspective_categories'] ?? null;

        if ($category) {
            return Override::resolveClass(PerspectiveCategory::class)::where('slug', trim($category))->first();
        }

        return null;
    }
    public function inertiaDependencies(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $data = $block->getData();
        $category = $this->getPerspectiveCategory();
        return [
            'user' => Inertia::optional(
                function () {
                    $user = $this->getUser();

                    return $user ? $this->userResource::make($user) : null;
                }
            ),
            'category'     => $this->perspectiveService->transformPerspectiveCategory($category),
            'perspectives' => $this->perspectiveService->categoryPerspectives($category, $this->getUser()),
            ...$this->buildPermalinks($data),
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();
        $data = $block->getData();

        $data = $block->getData();
        $category = $this->getPerspectiveCategory();
        return [
            'user' => $user ? $this->userResource::make($user) : null,
            'category'     => $this->perspectiveService->transformPerspectiveCategory($category),
            'perspectives' => $this->perspectiveService->categoryPerspectives($category, $this->getUser()),
            ...$this->buildPermalinks($data),
        ];
    }

    public function formRequestRules(
        PageColumnBlock $block,
    ): array {
        return [];
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
