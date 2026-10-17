<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tf-perspectives:prune-views --days=365')->monthly();
