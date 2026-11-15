<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;

class PerspectivesFeaturedBlock extends PerspectivesBlock
{
    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}
    public function inertiaDependencies(
        PageColumnBlock $block,
        Page $page,
    ): array {

        $validated = null;
        if ($this->hasValidator()) {
            $validated = $this->validator->validated();
        }
        $data = $block->getData();
        return [
            'user' => Inertia::optional(
                function () {
                    $user = $this->getUser();

                    return $user ? $this->userResource::make($user) : null;
                }
            ),
            'featured' => $this->perspectiveService
                ->setPaginate(true)
                ->setLimit((!empty($validated['featured_limit']) ? (int)$validated['featured_limit'] : 5))
                ->setPage((!empty($validated['featured_page']) ? (int)$validated['featured_page'] : 1))
                ->setPerPage((!empty($validated['featured_per_page']) ? (int)$validated['featured_per_page'] : 10))
                ->getFeaturedPerspectives($this->getUser()),
            ...$this->buildPermalinks($data),
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();

        $validated = null;
        if ($this->hasValidator()) {
            $validated = $this->validator->validated();
        }
        $data = $block->getData();
        return [
            'user' => $user ? $this->userResource::make($user) : null,
            'featured' => $this->perspectiveService
                ->setPaginate(true)
                ->setLimit((!empty($validated['featured_limit']) ? (int)$validated['featured_limit'] : 5))
                ->setPage((!empty($validated['featured_page']) ? (int)$validated['featured_page'] : 1))
                ->setPerPage((!empty($validated['featured_per_page']) ? (int)$validated['featured_per_page'] : 10))
                ->getFeaturedPerspectives($user),
            ...$this->buildPermalinks($data),
        ];
    }

    public function formRequestRules(
        PageColumnBlock $block,
    ): array {
        return [
            'featured_limit' => ['sometimes', 'integer', 'min:1'],
            'featured_page' => ['sometimes', 'integer', 'min:1'],
            'featured_per_page' => ['sometimes', 'integer', 'min:1'],
            'featured_sort' => ['sometimes', 'string', 'in:asc,desc'],
            'featured_filter' => ['sometimes', 'string'],
        ];
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
