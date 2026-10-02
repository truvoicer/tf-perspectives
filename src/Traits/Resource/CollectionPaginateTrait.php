<?php

namespace Truvoicer\TfPerspectives\Traits\Resource;

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Truvoicer\TfPerspectives\Enums\Resource\PaginationField;

trait CollectionPaginateTrait
{
    public function hasPagination(): bool
    {
        return $this->resource instanceof AbstractPaginator;
    }

    public function buildLinks(LengthAwarePaginator $resource)
    {
        return [
            PaginationField::TOTAL_PAGES->value => ceil($resource->total() / $resource->perPage()),
            PaginationField::TOTAL_ITEMS->value => $resource->total(),
            PaginationField::PAGE_SIZE->value => $resource->perPage(),
            PaginationField::PAGE_NUMBER->value => $resource->currentPage(),
            PaginationField::PREV_PAGE->value => $resource->currentPage() - 1,
            PaginationField::NEXT_PAGE->value => $resource->currentPage() + 1,
            PaginationField::LAST_PAGE->value => $resource->lastPage(),
            PaginationField::HAS_MORE->value => $resource->hasMorePages(),
            PaginationField::PAGINATION_TYPE->value => 'page',

        ];
    }
}
