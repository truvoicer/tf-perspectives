<?php

namespace Truvoicer\TfPerspectives\Traits\Database;

trait PaginationTrait
{
    public bool $paginate = false;

    public int $perPage = 10;

    public int $page;

    public int $total = 0;

    public function setPagination(bool $paginate = false): static
    {
        $this->paginate = $paginate;

        return $this;
    }

    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;

        return $this;
    }
}
