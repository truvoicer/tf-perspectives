<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Contracts\Override;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;
use Truvoicer\TruFetcherCore\Services\Page\Block\PageBlock;

class PerspectivesThreadBlock extends PageBlock
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
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();

        $parameters = $this->getParameters();
        $perspective = $this->getPerspective();
        return [
            'rootId'       => $perspective?->rootId(),
            'maxDepth'     => Perspective::MAX_DEPTH,
            'perspectives' => $this->perspectiveService->getPerspectiveThread($perspective, $this->getUser()),
            'user' => $user ? $this->userResource::make($user) : null,
            'perspective' => $perspective,
        ];
    }

    public function formRequestRules(
        PageColumnBlock $block,
    ): array {
        return [];
    }
}
