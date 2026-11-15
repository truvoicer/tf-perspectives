<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Contracts\Override;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;

class PerspectivesThreadBlock extends PerspectivesBlock
{
    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}
    private function getPerspective(): ?Perspective
    {
        $parameters = $this->getParameters();
        $perspectives = $parameters['perspectives'] ?? null;

        if ($perspectives) {
            return Override::resolveClass(Perspective::class)::find((int) $perspectives);
        }

        return null;
    }
    public function inertiaDependencies(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $parameters = $this->getParameters();
        $perspective = $this->getPerspective();
        $data = $block->getData();

        return [
            'rootId'       => $perspective?->rootId(),
            'maxDepth'     => Perspective::MAX_DEPTH,
            'perspectives' => $this->perspectiveService->getPerspectiveThread($perspective, $this->getUser()),
            'user' => Inertia::optional(
                function () {
                    $user = $this->getUser();

                    return $user ? $this->userResource::make($user) : null;
                }
            ),
            'perspective' => $perspective,
            ...$this->buildPermalinks($data),
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();
        $data = $block->getData();
        $parameters = $this->getParameters();
        $perspective = $this->getPerspective();
        return [
            'rootId'       => $perspective?->rootId(),
            'maxDepth'     => Perspective::MAX_DEPTH,
            'perspectives' => $this->perspectiveService->getPerspectiveThread($perspective, $this->getUser()),
            'user' => $user ? $this->userResource::make($user) : null,
            'perspective' => $perspective,
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
