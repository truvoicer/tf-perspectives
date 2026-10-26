<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Truvoicer\TfPerspectives\Http\Middleware\HandleInertiaRequests;
use Truvoicer\TfPerspectives\Http\Middleware\UsePerspectivesMorphMap;

Route::middleware([
    UsePerspectivesMorphMap::class,
    HandleInertiaRequests::class,  // runs after the swap
    SubstituteBindings::class, // Resolves {entity} into Entity model instance
])->group(function () {
    require __DIR__.'/web/perspectives.php';
});
