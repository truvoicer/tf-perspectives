<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Contracts\Override;
use Truvoicer\TruFetcherCore\Http\Resources\Page\PageResource;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;

class PerspectivesFeedBlock extends PerspectivesBlock
{

    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}

    public function inertiaDependencies(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $data = $block->getData();

        $validated = $this->getValidator()?->validated();
        $search = (!empty($validated['q'])) ? $validated['q'] : '';
        $tab = (!empty($validated['tab'])) ? $validated['tab'] : 'default';

        return [
            'user' => Inertia::optional(
                function () {
                    $user = $this->getUser();

                    return $user ? $this->userResource::make($user) : null;
                }
            ),

            'perspectives' => $this->perspectiveService->setPaginate(true)->fetchPerspectives(
                $this->getUser(),
                $search ?? '',
                $tab ?? 'default'
            ),
            'filters'      => ['q' => $search, 'tab' => $tab],
            ...$this->buildPermalinks($data),
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();
        $data = $block->getData();

        $validated = $this->getValidator()?->validated();
        $search = (!empty($validated['q'])) ? $validated['q'] : '';
        $tab = (!empty($validated['tab'])) ? $validated['tab'] : 'default';
        return [
            'user' => $user ? $this->userResource::make($user) : null,
            'perspectives' => $this->perspectiveService->setPaginate(true)->fetchPerspectives(
                $user,
                $search ?? '',
                $tab ?? 'default'
            ),
            'filters'      => ['q' => $search, 'tab' => $tab],
            ...$this->buildPermalinks($data),
        ];
    }

    public function formRequestRules(
        PageColumnBlock $block,
    ): array {
        return [
            'q' => ['nullable', 'sometimes', 'string'],
            'tab' => ['sometimes', 'string'],
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
