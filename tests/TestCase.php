<?php

// packages/truvoicer/tru-fetcher-core/tests/phpunit/TestCase.php

namespace Truvoicer\TfPerspectives\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Truvoicer\TfDbReadCore\Models\Role;
use Truvoicer\TfDbReadCore\Models\User as TfUser;
use Truvoicer\TfDbReadCore\Services\Auth\AuthService;
use Truvoicer\TfPerspectives\Database\Seeders\PermissionSeeder;
use Truvoicer\TfPerspectives\Database\Seeders\RoleSeeder;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\Doubles\TestUser;
use Truvoicer\TfPerspectives\TfPerspectivesServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected static bool $migrationsRun = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up view paths before anything else
        $this->setupViewPaths();

        // Force SQLite configuration

        $this->app['config']->set('tf-db-read-core.user_email', 'test@testuser.com');
        $this->app['config']->set('database.default', 'mysql');
        $this->app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // Override mysql connection to use SQLite
        $this->app['config']->set('database.connections.mysql', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $this->app['config']->set('database.connections.tf_mysql', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // Purge connections to force reconnection
        $this->app['db']->purge('sqlite');
        $this->app['db']->purge('mysql');

        // Reconnect
        $this->app['db']->connection('sqlite')->reconnect();

        // Run migrations once for all tests
        $this->runMigrations();

        // Begin transaction for test isolation
        DB::beginTransaction();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->artisan('db:seed', [
            '--database' => 'tf_mysql', // Specify your connection
            '--class' => 'Truvoicer\TfDbReadCore\Database\Seeders\RoleSeeder',
        ]);

        // Or for multiple seeders
        $this->artisan('db:seed', [
            '--database' => 'tf_mysql',
            '--class' => 'Truvoicer\TfDbReadCore\Database\Seeders\PropertySeeder',
        ]);

        // Bind the mock to the container
        $this->app->instance('App\Models\User', new TestUser);
        $envUserEmail = config('tf-db-read-core.user_email');
        $adminRole = Role::where('name', AuthService::ABILITY_ADMIN)->first();
        $fetcherApiUser = TfUser::create([
            'email' => $envUserEmail,
            'password' => 'password',
        ]);

        $fetcherApiUser->roles()->attach($adminRole->id);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
            TfPerspectivesServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Force SQLite configuration
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('database.connections.mysql', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('database.connections.tf_mysql', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // Setup broadcasting for testing
        $app['config']->set('broadcasting.default', 'log');

        // Setup queue for testing
        $app['config']->set('queue.default', 'sync');

        // Setup auth for testing - you'll need to create a User model in your package
        $app['config']->set('auth.providers.users.model', User::class);

        // Configure Inertia for testing
        $app['config']->set('inertia.testing', true);
        $app['config']->set('inertia.ssr.enabled', false);
        $app['config']->set('inertia.version', null);
        $app['config']->set('inertia.root_view', 'app');

        // Ensure environment is testing
        $app['env'] = 'testing';
    }

    protected function runMigrations(): void
    {
        // Run migrations for your package on sqlite
        $packageMigrationsPath = realpath(__DIR__.'/../src/Database/Migrations');

        if ($packageMigrationsPath && file_exists($packageMigrationsPath)) {
            // $this->loadMigrationsFrom($packageMigrationsPath);
            $this->artisan('migrate:fresh', [
                '--database' => 'mysql',
                '--path' => $packageMigrationsPath,
                '--realpath' => true, // This tells Laravel to treat the path as absolute
                '--force' => true,
            ]);
        }

        // Run migrations for tf-db-read-core package on tf_mysql connection
        $tfDbReadCoreMigrationsPath = realpath(__DIR__.'/../vendor/truvoicer/tf-db-read-core/src/Database/Migrations');

        if ($tfDbReadCoreMigrationsPath && file_exists($tfDbReadCoreMigrationsPath)) {
            // Load migrations from tf-db-read-core package
            // $this->loadMigrationsFrom($tfDbReadCoreMigrationsPath);
            $this->artisan('migrate', [
                '--database' => 'tf_mysql',
                '--path' => $tfDbReadCoreMigrationsPath,
                '--realpath' => true, // This tells Laravel to treat the path as absolute
                '--force' => true,
            ]);
        }
    }

    protected function setupViewPaths(): void
    {
        // Create views directory if it doesn't exist
        $viewsPath = __DIR__.'/views';

        if (! is_dir($viewsPath)) {
            mkdir($viewsPath, 0755, true);
        }

        // Create the root view if it doesn't exist
        $rootViewPath = $viewsPath.'/app.blade.php';

        if (! file_exists($rootViewPath)) {
            file_put_contents(
                $rootViewPath,
                <<<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
BLADE
            );
        }

        // Get existing view paths and ensure they're all strings
        $existingPaths = $this->app['config']->get('view.paths', []);

        // Filter to only include strings and ensure they're valid paths
        $validPaths = array_filter($existingPaths, function ($path) {
            return is_string($path) && is_dir($path);
        });

        // Add our test views path
        $validPaths[] = $viewsPath;

        // Set the cleaned view paths
        $this->app['config']->set('view.paths', $validPaths);

        // Set Inertia root view
        $this->app['config']->set('inertia.testing.page_paths', [$viewsPath]);

        // Also set the root view name
        $this->app['config']->set('inertia.root_view', 'app');
    }
}
