<?php
// packages/truvoicer/tf-perspectives/src/Console/PruneOldPerspectiveViews.php

namespace Truvoicer\TfPerspectives\Commands;

use Illuminate\Console\Command;
use Truvoicer\TfPerspectives\Models\PerspectiveView;

class PruneOldPerspectiveViews extends Command
{
    protected $signature = 'tf-perspectives:prune-views {--days=365}';
    protected $description = 'Delete perspective views older than N days.';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $deleted = PerspectiveView::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} perspective views older than {$cutoff->toDateString()}.");

        return self::SUCCESS;
    }
}
