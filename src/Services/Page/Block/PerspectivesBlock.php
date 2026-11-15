<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Truvoicer\TruFetcherCore\Contracts\Override;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Services\Page\Block\PageBlock;

class PerspectivesBlock extends PageBlock
{

    protected function buildPermalinks(array $data, ?array $pageTypes = [
        'thread_page',
        'feed_page',
        'categories_page',
        'category_thread_page',
        'search_page',
        'search_results_page',
    ]): array
    {
        $links = [];
        $parsePageTypes = $this->parsePageTypes($data, $pageTypes, 'model');
        foreach ($pageTypes as $key => $type) {
            if (!empty($parsePageTypes[$type]) && is_object($parsePageTypes[$type]) && $parsePageTypes[$type]::class === Override::resolveClass(Page::class)) {
                switch ($type) {
                    case 'thread_page':
                        $links['threadPagePermalink'] = $parsePageTypes[$type]->getPermalink();
                        break;
                    case 'feed_page':
                        $links['feedPagePermalink'] = $parsePageTypes[$type]->getPermalink();
                        break;
                    case 'categories_page':
                        $links['categoriesPagePermalink'] = $parsePageTypes[$type]->getPermalink();
                        break;
                    case 'category_thread_page':
                        $links['categoryThreadPagePermalink'] = $parsePageTypes[$type]->getPermalink();
                        break;
                    case 'search_page':
                        $links['searchPagePermalink'] = $parsePageTypes[$type]->getPermalink();
                        break;
                    case 'search_results_page':
                        $links['searchResultsPagePermalink'] = $parsePageTypes[$type]->getPermalink();
                        break;
                }
            }
        }
        return $links;
    }
}
