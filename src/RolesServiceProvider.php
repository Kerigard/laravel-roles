<?php

namespace Kerigard\LaravelRoles;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Kerigard\LaravelRoles\Contracts\Permission;
use Kerigard\LaravelRoles\Contracts\Role;

class RolesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishFiles();
        $this->defineBladeDirectives();
        $this->defineMacros();
        $this->defineBindings();
        $this->defineGates();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/roles.php', 'roles');
    }

    /**
     * Publish files for package.
     *
     * @return void
     */
    private function publishFiles(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/roles.php' => config_path('roles.php'),
        ], 'roles-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_role_tables.php.stub' => database_path(sprintf('migrations/%s_create_role_tables.php', date('Y_m_d_His'))),
        ], 'roles-migrations');
    }

    /**
     * Define blade directives.
     *
     * @return void
     */
    private function defineBladeDirectives(): void
    {
        Blade::directive('is', function ($role) {
            return "<?php if (auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });
        Blade::directive('elseis', function ($role) {
            return "<?php elseif (auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });
        Blade::directive('endis', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('isany', function ($role) {
            return "<?php if (auth()->check() && auth()->user()->hasAnyRole({$role})): ?>";
        });
        Blade::directive('elseisany', function ($role) {
            return "<?php elseif (auth()->check() && auth()->user()->hasAnyRole({$role})): ?>";
        });
        Blade::directive('endisany', function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * Define macros.
     *
     * @return void
     */
    private function defineMacros(): void
    {
        Route::macro('is', function ($role) {
            /** @var \Illuminate\Routing\Route $this */
            $this->middleware(["is:{$role}"]);

            return $this;
        });
    }

    /**
     * Define bindings.
     *
     * @return void
     */
    private function defineBindings(): void
    {
        app()->bind(Role::class, config('roles.models.role'));
        app()->bind(Permission::class, config('roles.models.permission'));

        app()->singleton(GateContract::class, function ($app) {
            return new Gate($app, fn () => call_user_func($app['auth']->userResolver()));
        });
    }

    /**
     * Define gates.
     *
     * @return void
     */
    private function defineGates(): void
    {
        if (config('roles.super_admin.enabled')) {
            $method = config('roles.super_admin.defer') ? 'after' : 'before';
            app(GateContract::class)->$method(function ($user) {
                if (method_exists($user, 'hasRole')) {
                    return $user->hasRole(config('roles.super_admin.slug')) ?: null;
                }
            });
        }

        app(GateContract::class)->before(function ($user, $abilities) {
            if (method_exists($user, 'checkPermission')) {
                return $user->checkPermission($abilities) ?: null;
            }
        });
    }
}
