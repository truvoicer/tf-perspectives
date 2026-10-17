<?php

use Illuminate\Support\Facades\Route;
use Truvoicer\TfPerspectives\Http\Controllers\AuthController;

use Truvoicer\TfPerspectives\Http\Controllers\{
    BookmarkController,
    DashboardController,
    FollowController,
    NotificationController,
    PerspectiveController,
    ProfileController,
    ReactionController,
    ReportController,
    SearchController,
    EmpathyController,
    PerspectiveCategoryController,
    ShoeController,
};


Route::prefix('perspectives')->name('perspectives.')->group(function () {

    Route::get('/login', function () {
        return inertia('@tf-perspectives::auth', ['mode' => 'login']);
    })
        ->name('login');

    Route::get('/register', function () {
        return inertia('@tf-perspectives::auth', ['mode' => 'register']);
    })
        ->name('register');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
    Route::get('/', [PerspectiveController::class, 'index'])->name('index');

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::post('/', [PerspectiveController::class, 'store'])->name('store');

        Route::get('/u/{user}', [ProfileController::class, 'show'])->name('profile');
        Route::post('/u/{user}/follow', [FollowController::class, 'toggle'])->name('profile.follow');


        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/bookmark', [BookmarkController::class, 'index'])->name('bookmark.index');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');


        Route::prefix('{perspective}')->group(function () {
            Route::patch('/', [PerspectiveController::class, 'update'])->name('update');
            Route::post('/empathize', [EmpathyController::class, 'toggle'])->name('empathize');
            Route::post('/react', [ReactionController::class, 'react'])->name('react');
            Route::post('/bookmark', [BookmarkController::class, 'toggle'])->name('bookmark.toggle');
            Route::post('/report', [ReportController::class, 'store'])->name('report');

            Route::post('/shoes', [ShoeController::class, 'store'])
                ->name('shoes.store');

            Route::put('/shoes/{perspectiveShoe}', [ShoeController::class, 'update'])
                ->name('shoes.update');
            Route::delete('/shoes/{perspectiveShoe}', [ShoeController::class, 'destroy'])
                ->name('shoes.destroy');

            Route::post('/shoes/{perspectiveShoe}/discussions', [ShoeController::class, 'discuss'])
                ->name('shoes.discussions.store');
            Route::delete('/discussions/{discussion}', [ShoeController::class, 'destroyDiscussion'])
                ->name('shoes.discussions.destroy');
        });
    });

    Route::get('/categories', [PerspectiveCategoryController::class, 'index'])->name('categories');
    Route::get('/categories/{slug}', [PerspectiveCategoryController::class, 'show'])->name('categories.show');

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::prefix('{perspective}')->group(function () {
        Route::get('/', [PerspectiveController::class, 'show'])->name('show');
    });
});
