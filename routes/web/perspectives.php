<?php

use Illuminate\Support\Facades\Route;

use Truvoicer\TfPerspectives\Http\Controllers\{
    AuthController, BookmarkController, CategoryController,
    DashboardController, FollowController, NotificationController,
    PerspectiveController, ProfileController, ReactionController,
    ReportController, SearchController, EmpathyController,
};

Route::middleware('web')->group(function () {
    Route::get('/',                [PerspectiveController::class, 'index'])->name('tf-perspectives.feed');
    Route::get('/perspectives/{perspective}', [PerspectiveController::class, 'show'])->name('tf-perspectives.show');

    Route::post('/perspectives/{perspective}/empathize', [EmpathyController::class, 'toggle'])->name('perspectives.empathize');
    Route::get('/categories',        [CategoryController::class, 'index'])->name('tf-perspectives.categories');
    Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('tf-perspectives.categories.show');

    Route::get('/search',  [SearchController::class, 'index'])->name('tf-perspectives.search');

    Route::get('/u/{handle}',         [ProfileController::class, 'show'])->name('tf-perspectives.profile');
    Route::post('/u/{handle}/follow', [FollowController::class, 'toggle'])->name('tf-perspectives.profile.follow');

    Route::middleware('guest')->group(function () {
        Route::get('/login',   fn () => inertia('@tf-perspectives::auth', ['mode' => 'login']))->name('tf-perspectives.login');
        Route::get('/register', fn () => inertia('@tf-perspectives::auth', ['mode' => 'register']))->name('tf-perspectives.register');
        Route::post('/login',    [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('tf-perspectives.logout');

        Route::post('/perspectives',                  [PerspectiveController::class, 'store'])->name('tf-perspectives.store');
        Route::post('/perspectives/{perspective}/react',    [ReactionController::class, 'react'])->name('tf-perspectives.react');
        Route::post('/perspectives/{perspective}/bookmark', [BookmarkController::class, 'toggle'])->name('tf-perspectives.bookmark');
        Route::post('/perspectives/{perspective}/report',   [ReportController::class, 'store'])->name('tf-perspectives.report');

        Route::get('/dashboard',  [DashboardController::class, 'index'])->name('tf-perspectives.dashboard');
        Route::get('/bookmarks',  [BookmarkController::class, 'index'])->name('tf-perspectives.bookmarks');

        Route::get('/notifications',             [NotificationController::class, 'index'])->name('tf-perspectives.notifications');
        Route::post('/notifications/read-all',   [NotificationController::class, 'readAll'])->name('tf-perspectives.notifications.read-all');
        Route::post('/notifications/{id}/read',  [NotificationController::class, 'read'])->name('tf-perspectives.notifications.read');
    });
});
