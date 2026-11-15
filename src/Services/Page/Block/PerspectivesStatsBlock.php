<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;

class PerspectivesStatsBlock extends PerspectivesBlock
{
    const DEFAULT_PER_PAGE = 4;

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
            'stats' => $this->perspectiveService
                ->setPaginate(true)
                ->setPage((!empty($validated['stats_page']) ? (int)$validated['stats_page'] : 1))
                ->setPerPage((!empty($validated['stats_per_page']) ? (int)$validated['stats_per_page'] : self::DEFAULT_PER_PAGE))
                ->getPerspectiveStats(),
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
            'stats'        => $this->perspectiveService
                ->setPaginate(true)
                ->setPage((!empty($validated['stats_page']) ? (int)$validated['stats_page'] : 1))
                ->setPerPage((!empty($validated['stats_per_page']) ? (int)$validated['stats_per_page'] : self::DEFAULT_PER_PAGE))
                ->getPerspectiveStats(),
            ...$this->buildPermalinks($data),
        ];
    }

    public function formRequestRules(
        PageColumnBlock $block,
    ): array {
        return [
            'stats_page' => ['sometimes', 'numeric'],
            'stats_per_page' => ['sometimes', 'numeric'],
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
