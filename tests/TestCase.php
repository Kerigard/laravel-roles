<?php

namespace Kerigard\LaravelRoles\Tests;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kerigard\LaravelRoles\RolesServiceProvider;
use Kerigard\LaravelRoles\Tests\Models\User;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var bool
     */
    protected $superAdminIsDefer = false;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrations();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [RolesServiceProvider::class];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('view.paths', [__DIR__.'/resources/views']);

        $app['config']->set('auth.guards.api', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        $app['config']->set('roles.models.role', \Kerigard\LaravelRoles\Tests\Models\Role::class);
        $app['config']->set('roles.models.permission', \Kerigard\LaravelRoles\Tests\Models\Permission::class);
        $app['config']->set('roles.models.user', \Kerigard\LaravelRoles\Tests\Models\User::class);
        $app['config']->set('roles.super_admin.enabled', true);
        $app['config']->set('roles.super_admin.defer', $this->superAdminIsDefer);
    }

    /**
     * @return void
     */
    private function loadMigrations(): void
    {
        $migration = require __DIR__.'/../database/migrations/create_role_tables.php.stub';
        $migration->up();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    /**
     * @param  array  $attributes
     * @return \Kerigard\LaravelRoles\Tests\Models\User
     */
    public function createUser(array $attributes = []): User
    {
        $user = User::fake($attributes);
        $this->actingAs($user);

        return $user;
    }

    /**
     * @param  string  $middleware
     * @param  string  $attribute
     * @return \Illuminate\Http\Response|\Exception|\Illuminate\Auth\Access\AuthorizationException
     */
    public function runMiddleware(string $middleware, string $attribute)
    {
        try {
            return app($middleware)->handle(new Request(), fn () => new Response(), $attribute);
        } catch (Exception $e) {
            return $e;
        }
    }
}
