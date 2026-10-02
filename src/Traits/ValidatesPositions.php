<?php

namespace Truvoicer\TfPerspectives\Traits;

use Truvoicer\TfPerspectives\Models\PageColumn;
use Truvoicer\TfPerspectives\Models\PageColumnBlock;
use Truvoicer\TfPerspectives\Models\PageRow;
use Truvoicer\TfPerspectives\Services\Page\Layout\Row\Column\Block\PageColumnBlockPositionService;
use Truvoicer\TfPerspectives\Services\Page\Layout\Row\Column\PageColumnPositionService;
use Truvoicer\TfPerspectives\Services\Page\Layout\Row\PageRowPositionService;

trait ValidatesPositions
{
    protected function validatePositionsBeforeMove($model): void
    {
        if ($model instanceof PageRow) {
            app(PageRowPositionService::class)->getPositionService()->validateAndFixPositions($model);
        } elseif ($model instanceof PageColumn) {
            app(PageColumnPositionService::class)->getPositionService()->validateAndFixPositions($model);
        } elseif ($model instanceof PageColumnBlock) {
            app(PageColumnBlockPositionService::class)->getPositionService()->validateAndFixPositions($model);
        }
    }
}
