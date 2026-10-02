<?php

namespace Truvoicer\TfPerspectives\Traits;

use Truvoicer\TfPerspectives\Models\Page;

trait PageTrait
{
    protected ?Page $page = null;

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }
}
