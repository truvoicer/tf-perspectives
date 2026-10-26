<?php
// packages/truvoicer/tf-perspectives/routes/web/perspectives.php

use Illuminate\Support\Facades\Route;
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
    VerificationController,
};
use Truvoicer\TruFetcherCore\Http\Controllers\Auth\Social\Facebook\FacebookLoginController;
use Truvoicer\TruFetcherCore\Http\Controllers\Auth\Social\Google\GoogleLoginController;

Route::prefix('perspectives')->name('perspectives.')->group(function () {


    Route::middleware('guest')->group(function () {

        Route::post('/auth/google/one-tap', [GoogleLoginController::class, 'store'])
            ->name('auth.google.one_tap');

        Route::post('/auth/facebook/login', [FacebookLoginController::class, 'store'])
            ->name('auth.facebook.login');


        Route::get('/login', function () {
            return inertia(
                config('tf-perspectives.auth_views.login', '@tf-perspectives::auth'),
                ['mode' => 'login'],
            );
        })->name('login');

        Route::get('/register', function () {
            return inertia(
                config('tf-perspectives.auth_views.register', '@tf-perspectives::auth'),
                ['mode' => 'register'],
            );
        })->name('register');

        Route::get('/forgot-password', function () {
            return inertia(
                config('tf-perspectives.auth_views.forgot_password', '@tf-perspectives::auth/forgot-password'),
            );
        })->name('password.request');

        Route::get('/reset-password/{token}', function (string $token) {
            return inertia(
                config('tf-perspectives.auth_views.reset_password', '@tf-perspectives::auth/reset-password'),
                ['token' => $token],
            );
        })->name('password.reset');
    });

    // ── Verified user views ─────────────────────────────────────────────
    Route::middleware('auth')->group(function () {
        Route::get('/verify-email', function () {
            return inertia(
                config('tf-perspectives.auth_views.verify_email', '@tf-perspectives::auth/verify-email'),
            );
        })->name('verification.notice');

        Route::get('/verify-email/{id}/{hash}', [VerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [VerificationController::class, 'send'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::get('/account', fn() => inertia('@tf-perspectives::account'))->name('account');
    });

    // ── Main app ────────────────────────────────────────────────────────
    Route::get('/', [PerspectiveController::class, 'index'])->name('index');

    Route::middleware('auth')->group(function () {
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

            Route::post('/shoes', [ShoeController::class, 'store'])->name('shoes.store');
            Route::put('/shoes/{perspectiveShoe}', [ShoeController::class, 'update'])->name('shoes.update');
            Route::delete('/shoes/{perspectiveShoe}', [ShoeController::class, 'destroy'])->name('shoes.destroy');
            Route::post('/shoes/{perspectiveShoe}/discussions', [ShoeController::class, 'discuss'])->name('shoes.discussions.store');
            Route::delete('/discussions/{discussion}', [ShoeController::class, 'destroyDiscussion'])->name('shoes.discussions.destroy');
        });
    });

    Route::get('/categories', [PerspectiveCategoryController::class, 'index'])->name('categories');
    Route::get('/categories/{slug}', [PerspectiveCategoryController::class, 'show'])->name('categories.show');

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::prefix('{perspective}')->group(function () {
        Route::get('/', [PerspectiveController::class, 'show'])->name('show');
    });
});
