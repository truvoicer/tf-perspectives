<?php

namespace Truvoicer\TfPerspectives\Factories\Page\Block;

use Truvoicer\TruFetcherCore\Contracts\Page\Block\PageBlockInterface;
use Truvoicer\TfPerspectives\Enums\Block\Block;
use Truvoicer\TfPerspectives\Services\Page\Block\PerspectivesCategoryBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\PerspectivesFeaturedBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\PerspectivesFeedBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\PerspectivesStatsBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\PerspectivesThreadBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\PerspectivesSearchBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\PerspectivesSearchResultsBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\PerspectivesCategoryThreadBlock;
use Truvoicer\TruFetcherCore\Factories\Page\Block\PageBlockFactory as BlockPageBlockFactory;

class PageBlockFactory extends BlockPageBlockFactory
{
    public static function make(
        string $blockType,
        ?array $data = []
    ): ?PageBlockInterface {

        return match ($blockType) {
            Block::PERSPECTIVES_CATEGORY->value => app()->make(PerspectivesCategoryBlock::class)->setData($data ?? []),
            Block::PERSPECTIVES_CATEGORY_THREAD->value => app()->make(PerspectivesCategoryThreadBlock::class)->setData($data ?? []),
            Block::PERSPECTIVES_FEED->value => app()->make(PerspectivesFeedBlock::class)->setData($data ?? []),
            Block::PERSPECTIVES_STATS->value => app()->make(PerspectivesStatsBlock::class)->setData($data ?? []),
            Block::PERSPECTIVES_THREAD->value => app()->make(PerspectivesThreadBlock::class)->setData($data ?? []),
            Block::PERSPECTIVES_FEATURED->value => app()->make(PerspectivesFeaturedBlock::class)->setData($data ?? []),
            Block::PERSPECTIVES_SEARCH->value => app()->make(PerspectivesSearchBlock::class)->setData($data ?? []),
            Block::PERSPECTIVES_SEARCH_RESULTS->value => app()->make(PerspectivesSearchResultsBlock::class)->setData($data ?? []),
            default => parent::make($blockType, $data)
        };
    }
}
