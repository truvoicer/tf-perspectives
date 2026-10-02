<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::middleware([
    SubstituteBindings::class, // Resolves {entity} into Entity model instance
])->group(function () {

    require __DIR__.'/web/perspectives.php';

});
