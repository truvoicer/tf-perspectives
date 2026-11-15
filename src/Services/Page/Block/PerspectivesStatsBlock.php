<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;
use Truvoicer\TruFetcherCore\Services\Page\Block\PageBlock;

class PerspectivesStatsBlock extends PageBlock
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

        return [
            'user' => $user ? $this->userResource::make($user) : null,
            'stats'        => $this->perspectiveService
                ->setPaginate(true)
                ->setPage((!empty($validated['stats_page']) ? (int)$validated['stats_page'] : 1))
                ->setPerPage((!empty($validated['stats_per_page']) ? (int)$validated['stats_per_page'] : self::DEFAULT_PER_PAGE))
                ->getPerspectiveStats(),
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
}
