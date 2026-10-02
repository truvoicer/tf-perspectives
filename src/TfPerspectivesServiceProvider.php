<?php

namespace Truvoicer\TfPerspectives;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route as FacadesRoute;
use Inertia\Inertia;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Truvoicer\TfPerspectives\Providers\EventServiceProvider;

class TfPerspectivesServiceProvider extends PackageServiceProvider
{
    protected array $listen = [];

    public string $packagePath;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('tf-perspectives')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_tf_perspectives_table');
    }

    public function register(): void
    {
        // 1. MUST call parent::register() so Spatie instantiates $this->package and runs configurePackage()
        parent::register();

        // Merge package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/tf-perspectives.php',
            'tf-perspectives'
        );

    }


    /**
     * Check if the given class string is a controller.
     */
    protected function isController(string $class): bool
    {
        return str_contains($class, 'Controller') || str_contains($class, 'Http\\Controllers\\');
    }

    /**
     * Register view composers
     */
    protected function registerViewComposers(): void
    {
        // Skip view composer registration during tests
        if ($this->app->environment('testing')) {
            return;
        }

        // // Only register if the 'app' view exists
        // if (View::exists('app')) {
        //     View::composer('app', ThemeComposer::class);
        // }
    }

    protected function registerFilesystemDisks(): void
    {
        // Get package's default disk configurations
        $mainAppDisks = config('tf-perspectives.filesystems.disks', []);

        // Get existing disk configurations from the application
        $packageConfig = require __DIR__.'/../config/tf-perspectives.php';
        $packageDisks = (
            ! empty($packageConfig['filesystems']['disks']) &&
            is_array($packageConfig['filesystems']['disks'])
        ) ? $packageConfig['filesystems']['disks'] : [];

        // Merge configurations: application disks take precedence, but package disks act as fallbacks
        $mergedDisks = array_merge($packageDisks, $mainAppDisks);

        // Set the merged disks back to the config
        config()->set('tf-perspectives.filesystems.disks', $mergedDisks);
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // 1. MUST call parent::boot() so Spatie registers package views, configs, etc.
        parent::boot();

        $this->packagePath = dirname(__DIR__);

        // Use Laravel's global config to store this value
        $this->app['config']->set('tf-perspectives.path', $this->packagePath);

        $this->publishes([
            __DIR__.'/../config/tf-perspectives.php' => config_path(
                'tf-perspectives.php'
            ),
        ], 'tf-perspectives-config');

        $this->publishes([
            __DIR__.'/Database/Migrations' => database_path('migrations'),
        ], 'tf-perspectives-migrations');

        // Register view composers
        $this->registerViewComposers();

        // load commands directory
        // if ($this->app->runningInConsole()) {
        //     $this->commands([

        //     ]);
        // }

        $this->shareDataWithInertia();
        $this->bootRoutes();
        $this->bootEventListeners();
        // Register broadcasting channels
        $this->bootBroadcasting();
    }


    /**
     * Boot broadcasting channels
     */
    protected function bootBroadcasting(): void
    {
        // Check if broadcasting is enabled in config

        if (! config('tf-perspectives.broadcasting.enabled', true)) {
            return;
        }

        // Register broadcast routes
        Broadcast::routes(['middleware' => ['web', 'auth']]);

        // Load broadcast channels if they exist
        $this->registerDefaultChannels();
    }

    /**
     * Register default broadcasting channels
     */
    protected function registerDefaultChannels(): void
    {
        // Register comment channel
        // Broadcast::channel('comments.{provider}.{service}.{itemId}', function ($user, $provider, $service, $itemId) {
        //     // Public channel - anyone can listen
        //     // But you can add custom logic here if needed
        //     return true;
        // });

        // // Register user private channel
        // Broadcast::channel('user.{userId}', function ($user, $userId) {
        //     return (int) $user->getId() === (int) $userId;
        // });

        // // Register comment notifications channel (for comment owners)
        // Broadcast::channel('comment.{commentId}', function ($user, $commentId) {
        //     // Allow comment owner and admins to listen
        //     $comment = Comment::find($commentId);
        //     if (! $comment) {
        //         return false;
        //     }

        //     return $user->getId() === $comment->user_id ||
        //         $user->hasRole([Role::ADMIN->value, Role::SUPERUSER->value]);
        // });
    }

    protected function bootRoutes(): void
    {
        if (config('tf-perspectives.load_routes', true)) {
            FacadesRoute::middleware('web')
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
                });
            FacadesRoute::prefix('api')
                ->middleware('api')
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
                });
        }
    }

    protected function bootEventListeners(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    protected function shareDataWithInertia(): void
    {
        // Only run if inertia-laravel is installed
        if (! class_exists(Inertia::class)) {
            return;
        }

        // Share data using a key namespaced to your package
        Inertia::share('tfPerspectives', [
            'version' => '1.0.0',
            'enabled' => config('tf-perspectives.enabled', true),
            'broadcasting' => [
                'enabled' => config('tf-perspectives.broadcasting.enabled', true),
                'key' => config('tf-perspectives.broadcasting.connections.reverb.key'),
                'host' => config('tf-perspectives.broadcasting.connections.reverb.options.host'),
                'port' => config('tf-perspectives.broadcasting.connections.reverb.options.port'),
                'scheme' => config('tf-perspectives.broadcasting.connections.reverb.options.scheme'),
            ],
        ]);
    }
}
