<?php

namespace Truvoicer\TfPerspectives;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route as FacadesRoute;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Truvoicer\TfDbReadCore\Models\User as ModelsUser;
use Truvoicer\TfPerspectives\Analytics\AnalyticsManager;
use Truvoicer\TfPerspectives\Commands\CreateUser;
use Truvoicer\TfPerspectives\Commands\MenuCleaner;
use Truvoicer\TfPerspectives\Commands\MenuSeed;
use Truvoicer\TfPerspectives\Commands\RepositoryMakeCommand;
use Truvoicer\TfPerspectives\Commands\SeedCommentsCommand;
use Truvoicer\TfPerspectives\Commands\SyncCommentLikesCount;
use Truvoicer\TfPerspectives\Commands\SyncCommentRepliesCount;
use Truvoicer\TfPerspectives\Commands\SyncDatabaseEnum;
use Truvoicer\TfPerspectives\Commands\TestMail;
use Truvoicer\TfPerspectives\Commands\VerifyCommentCounts;
use Truvoicer\TfPerspectives\Contracts\Override;
use Truvoicer\TfPerspectives\Contracts\OverrideRegistryInterface;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Events\Comment\CommentCreated;
use Truvoicer\TfPerspectives\Events\Comment\CommentDeleted;
use Truvoicer\TfPerspectives\Events\Comment\CommentLiked;
use Truvoicer\TfPerspectives\Events\Comment\CommentStatsUpdated;
use Truvoicer\TfPerspectives\Events\Comment\CommentUpdated;
use Truvoicer\TfPerspectives\Events\Email\EmailSubscriptionCreated;
use Truvoicer\TfPerspectives\Listeners\Email\AddSubscriberToHubSpot;
use Truvoicer\TfPerspectives\Listeners\Email\NotifyUserSubscriptionCreated;
use Truvoicer\TfPerspectives\Listeners\Track\Email\TrackEmailSubscriptionCreated;
use Truvoicer\TfPerspectives\Models\Application;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\Entity;
use Truvoicer\TfPerspectives\Models\File;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\PageColumn;
use Truvoicer\TfPerspectives\Models\PageColumnBlock;
use Truvoicer\TfPerspectives\Models\PageRow;
use Truvoicer\TfPerspectives\Models\Permission;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Providers\EventServiceProvider;
use Truvoicer\TfPerspectives\View\Composers\ThemeComposer;

class TfPerspectivesServiceProvider extends PackageServiceProvider
{
    protected $listen = [
        EmailSubscriptionCreated::class => [
            TrackEmailSubscriptionCreated::class,
            AddSubscriberToHubSpot::class,
            NotifyUserSubscriptionCreated::class,
        ],

        // Add your comment events
        CommentCreated::class => [
            // Add listeners here if needed
            // NotifyCommentOwner::class,
            // SendCommentNotification::class,
        ],

        CommentUpdated::class => [
            // Listeners for comment updates
        ],

        CommentDeleted::class => [
            // Listeners for comment deletions
        ],

        CommentLiked::class => [
            // Listeners for comment likes
        ],
        CommentStatsUpdated::class => [
            // Listeners for comment stats updates
        ],
    ];

    public string $packagePath;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('tru-fetcher-core')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_tru_fetcher_core_table');
    }

    public function register(): void
    {
        // 1. MUST call parent::register() so Spatie instantiates $this->package and runs configurePackage()
        parent::register();

        // Merge package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/tru-fetcher-core.php',
            'tru-fetcher-core'
        );

        // Register the override registry as a singleton
        $this->app->singleton(OverrideRegistryInterface::class, OverrideRegistry::class);

        $this->app->singleton(AnalyticsManager::class);
        $this->registerFilesystemDisks();

        // Bind the Override class to the container for dependency injection
        $this->app->alias(OverrideRegistryInterface::class, 'tru-fetcher-core.override');
        $this->registerOverridess();
    }

    protected function registerOverridess(): void
    {
        $overrides = config('tru-fetcher-core.overrides.classes', []);

        foreach ($overrides as $abstract => $concrete) {
            if ($abstract === $concrete) {
                continue;
            }

            $this->app->bind($abstract, $concrete);
        }
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

        // Only register if the 'app' view exists
        if (View::exists('app')) {
            View::composer('app', ThemeComposer::class);
        }
    }

    protected function registerFilesystemDisks(): void
    {
        // Get package's default disk configurations
        $mainAppDisks = config('tru-fetcher-core.filesystems.disks', []);

        // Get existing disk configurations from the application
        $packageConfig = require __DIR__.'/../config/tru-fetcher-core.php';
        $packageDisks = (
            ! empty($packageConfig['filesystems']['disks']) &&
            is_array($packageConfig['filesystems']['disks'])
        ) ? $packageConfig['filesystems']['disks'] : [];

        // Merge configurations: application disks take precedence, but package disks act as fallbacks
        $mergedDisks = array_merge($packageDisks, $mainAppDisks);

        // Set the merged disks back to the config
        config()->set('filesystems.disks', $mergedDisks);
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
        $this->app['config']->set('tru-fetcher-core.path', $this->packagePath);

        // Register overrides from config
        $this->registerOverrides();

        // 1. Superuser 'Master Key' 🔑
        // This 'before' gate runs before all other authorization checks.
        Gate::before(function (ModelsUser $user, string $ability) {
            if ($user->hasRole([Role::SUPERUSER->value])) {
                return true;
            }
        });

        // 2. Dynamic Gates 'The Bouncer's List'
        // Dynamically register a gate for each permission in the database.
        $this->registerDynamicGates();

        $this->publishes([
            __DIR__.'/../config/tru-fetcher-core.php' => config_path(
                'tru-fetcher-core.php'
            ),
        ], 'tru-fetcher-core-config');

        $this->publishes([
            __DIR__.'/Database/Migrations' => database_path('migrations'),
        ], 'tru-fetcher-core-migrations');

        // Register view composers
        $this->registerViewComposers();

        // load commands directory
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateUser::class,
                TestMail::class,
                RepositoryMakeCommand::class,
                SyncDatabaseEnum::class,
                MenuCleaner::class,
                SyncCommentLikesCount::class,
                SyncCommentRepliesCount::class,
                VerifyCommentCounts::class,
                SeedCommentsCommand::class,
                MenuSeed::class,
            ]);
        }

        $this->shareDataWithInertia();
        $this->bootRoutes();
        $this->registerRouteModelBindings();
        $this->bootEventListeners();

        // Register broadcasting channels
        $this->bootBroadcasting();
        $this->bindAllModels();
    }

    protected function bindAllModels(): void
    {
        // Model map - you can generate this dynamically
        $models = [
            'entity' => Entity::class,
            'application' => Application::class,
            'page_column_block' => PageColumnBlock::class,
        ];

        foreach ($models as $paramName => $defaultClass) {
            FacadesRoute::bind($paramName, function ($value) use ($defaultClass) {
                $resolvedClass = Override::resolveClass($defaultClass);

                return $resolvedClass::where('id', $value)->firstOrFail();
            });
        }
    }

    /**
     * Boot broadcasting channels
     */
    protected function bootBroadcasting(): void
    {
        // Check if broadcasting is enabled in config

        if (! config('tru-fetcher-core.broadcasting.enabled', true)) {
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
        Broadcast::channel('comments.{provider}.{service}.{itemId}', function ($user, $provider, $service, $itemId) {
            // Public channel - anyone can listen
            // But you can add custom logic here if needed
            return true;
        });

        // Register user private channel
        Broadcast::channel('user.{userId}', function ($user, $userId) {
            return (int) $user->getId() === (int) $userId;
        });

        // Register comment notifications channel (for comment owners)
        Broadcast::channel('comment.{commentId}', function ($user, $commentId) {
            // Allow comment owner and admins to listen
            $comment = Comment::find($commentId);
            if (! $comment) {
                return false;
            }

            return $user->getId() === $comment->user_id ||
                $user->hasRole([Role::ADMIN->value, Role::SUPERUSER->value]);
        });
    }

    protected function registerOverrides(): void
    {
        $overrides = config('tru-fetcher-core.overrides.classes', []);

        if (empty($overrides)) {
            return;
        }

        $registry = app(OverrideRegistryInterface::class);

        foreach ($overrides as $abstract => $concrete) {
            if (class_exists($abstract) && class_exists($concrete)) {
                $registry->register($abstract, $concrete);
            }
        }
    }

    protected function bootRoutes(): void
    {
        if (config('tru-fetcher-core.load_routes', true)) {
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

    protected function registerRouteModelBindings(): void
    {
        // Page row binding - must belong to the specified page
        FacadesRoute::bind('pageRow', function ($value, $route) {
            $pageId = $route->parameter('page');

            // If page is not a Page model instance, get the ID
            $pageId = $pageId instanceof Page ? $pageId->id : $pageId;

            return Override::resolveClass(PageRow::class)::where('id', $value)
                ->where('page_id', $pageId)
                ->firstOrFail();
        });

        // Page column binding - must belong to the specified page row
        FacadesRoute::bind('pageColumn', function ($value, $route) {
            $pageRow = $route->parameter('pageRow');

            // If pageRow is not a PageRow instance, we need to get the pageRow ID
            $pageRowId = $pageRow instanceof PageRow ? $pageRow->id : $pageRow;

            return Override::resolveClass(PageColumn::class)::where('id', $value)
                ->where('page_row_id', $pageRowId)
                ->firstOrFail();
        });

        // Page column block binding - must belong to the specified page column
        FacadesRoute::bind('pageColumnBlock', function ($value, $route) {
            $pageColumn = $route->parameter('pageColumn');

            // If pageColumn is not a PageColumn instance
            $pageColumnId = $pageColumn instanceof PageColumn ? $pageColumn->id : $pageColumn;

            return Override::resolveClass(PageColumnBlock::class)::where('id', $value)
                ->where('page_column_id', $pageColumnId)
                ->firstOrFail();
        });

        // Optional: File binding - must belong to the specified page column block
        FacadesRoute::bind('pageColumnBlockFile', function ($value, $route) {
            $pageColumnBlock = $route->parameter('pageColumnBlock');

            $pageColumnBlockId = $pageColumnBlock instanceof PageColumnBlock
                ? $pageColumnBlock->id
                : $pageColumnBlock;

            return File::where('id', $value)
                ->where('page_column_block_id', $pageColumnBlockId)
                ->firstOrFail();
        });
    }

    protected function registerDynamicGates(): void
    {
        // Only register gates if the permissions table exists
        if (! Schema::hasTable('permissions')) {
            return;
        }

        // Use a try-catch block to prevent errors during initial migrations
        try {
            // Use chunking for better performance with many permissions
            Permission::chunk(100, function ($permissions) {
                foreach ($permissions as $permission) {
                    Gate::define($permission->getName(), function (User $user) use ($permission) {
                        return $user->hasPermissionTo($permission);
                    });
                }
            });
        } catch (\Exception $e) {
            // Log the error silently - this is expected during migrations
            if (app()->has('log')) {
                app('log')->debug('Failed to register dynamic gates: '.$e->getMessage());
            }
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
        Inertia::share('truFetcherCore', [
            'version' => '1.0.0',
            'enabled' => config('tru-fetcher-core.enabled', true),
            'broadcasting' => [
                'enabled' => config('tru-fetcher-core.broadcasting.enabled', true),
                'key' => config('broadcasting.connections.reverb.key'),
                'host' => config('broadcasting.connections.reverb.options.host'),
                'port' => config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.connections.reverb.options.scheme'),
            ],
        ]);
    }
}
