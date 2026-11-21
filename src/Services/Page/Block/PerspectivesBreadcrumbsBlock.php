<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Contracts\Override;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;

class PerspectivesBreadcrumbsBlock extends PerspectivesBlock
{
    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}

    private function parseItems(array $data): array
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            return [];
        }

        // Collect all page IDs in one pass
        $pageIds = collect($data['items'])
            ->pluck('page_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        // Fetch all pages in a single query, keyed by id
        $pages = $pageIds->isEmpty()
            ? collect()
            : Page::whereIn('id', $pageIds)->get()->keyBy('id');

        return array_map(
            fn($item) => [
                'label' => $item['label'] ?? '',
                'href'  => !empty($item['page_id'])
                    ? $pages->get((int) $item['page_id'])?->getPermalink()
                    : null,
                'icon'  => $item['icon'] ?? null,
            ],
            $data['items']
        );
    }

    public function inertiaDependencies(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $data = $block->getData();

        return [
            'user' => Inertia::optional(
                function () {
                    $user = $this->getUser();

                    return $user ? $this->userResource::make($user) : null;
                }
            ),
            'items' => $this->parseItems($data),

            ...$this->buildPermalinks($data),
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();
        $data = $block->getData();

        return [
            'user'  => $user ? $this->userResource::make($user) : null,
            'items' => $this->parseItems($data),
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
