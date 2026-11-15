<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;
use Truvoicer\TruFetcherCore\Services\Page\Block\PageBlock;

class PerspectivesFeaturedBlock extends PageBlock
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
            'featured' => $this->perspectiveService
                ->setPaginate(true)
                ->setLimit((!empty($validated['featured_limit']) ? (int)$validated['featured_limit'] : 5))
                ->setPage((!empty($validated['featured_page']) ? (int)$validated['featured_page'] : 1))
                ->setPerPage((!empty($validated['featured_per_page']) ? (int)$validated['featured_per_page'] : 10))
                ->getFeaturedPerspectives($user),
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
}
