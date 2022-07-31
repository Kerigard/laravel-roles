# Laravel Roles

<p align="center">
  <a href="https://github.com/Kerigard/laravel-roles/actions"><img src="https://github.com/Kerigard/laravel-roles/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/Kerigard/laravel-roles"><img src="https://img.shields.io/packagist/dt/Kerigard/laravel-roles" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/Kerigard/laravel-roles"><img src="https://img.shields.io/packagist/v/Kerigard/laravel-roles" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/Kerigard/laravel-roles"><img src="https://img.shields.io/packagist/l/Kerigard/laravel-roles" alt="License"></a>
</p>

Permissions and roles for Laravel 9.20 and up.

## Installation

Install package via composer:

``` bash
composer require kerigard/laravel-roles
```

Publish the configuration and migration files using the `vendor:publish` artisan command:

```bash
php artisan vendor:publish --provider="Kerigard\LaravelRoles\RolesServiceProvider"
```

Customize the `roles.php` configuration file according to your requirements. After that run the migrations:

```bash
php artisan migrate
```

## Usage

### Connecting traits

To start using permission and role checking, your User model must use the `Kerigard\LaravelRoles\Traits\HasRoles` and `Kerigard\LaravelRoles\Traits\HasPermissions` traits:

```php
use Kerigard\LaravelRoles\Traits\HasPermissions;
use Kerigard\LaravelRoles\Traits\HasRoles;

class User extends Authenticatable
{
    use HasPermissions;
    use HasRoles;
}
```

> It is not necessary to connect both traits at the same time.

### Creating roles and permissions

Create roles and permissions, after which create a relationship between them:

```php
use Kerigard\LaravelRoles\Models\Permission;
use Kerigard\LaravelRoles\Models\Role;

$role = Role::create(['name' => 'Manager', 'slug' => 'manager']);
$permission = Permission::create(['name' => 'Edit articles', 'slug' => 'edit-articles']);
$role->attachPermission($permission);
```

> You can override models through a config file.

Connect a role or permission to a user:

```php
$user->attachRole($role);
// or
$user->attachPermission($permission);
```

### Permissions check

To check for permission, run:

```php
$user->hasPermission('edit-articles');
$user->hasPermission(1);
$user->hasPermission($permission);

// has all permissions
$user->hasPermission(['edit-articles', 'register-articles']);
// has any permissions
$user->hasAnyPermission(['edit-articles', 'register-articles']);
```

All permissions are registered with Laravel Gates, so you can use the `can` function:

```php
$user->can('edit-articles');
$user->can(['edit-articles', 'register-articles']);
$user->canAny(['edit-articles', 'register-articles']);
```

In a controller, you can use the `authorize` function to throw an exception if the user doesn't have permissions:

```php
class PostController extends Controller
{
    public function index()
    {
        $this->authorize('view-posts');

        return Post::all();
    }
}
```

### Roles check

To check if a role exists, run:

```php
$user->hasRole('manager');
$user->hasRole(1);
$user->hasRole($role);

// has all roles
$user->hasRole(['manager', 'admin']);
// kas any roles
$user->hasAnyRole(['manager', 'admin']);
```

You can also check if a role contains a permission:

```php
$role->hasPermission('edit-articles');
```

If you want to check the role in the controller and raise an exception if it is missing, then you need to replace the trait import in the `app\Http\Controllers\Controller.php` file:

```php
// from
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
//to
use Kerigard\LaravelRoles\Traits\AuthorizesRequests;
```

After that, you can use the `authorizeRole` function in all controllers to check the role:

```php
class PostController extends Controller
{
    public function index()
    {
        $this->authorizeRole('editor');

        return Post::all();
    }
}
```

### Blade directives

You can use directives in blade files to write conditions conveniently:

```php
@can('edit-articles')
    //
@endcan

@canany(['edit-articles', 'register-articles'])
    //
@endcanany

@is('manager')
    //
@endis

@isany(['manager', 'admin'])
    //
@endisany
```

### Middlewares

In the `app/Http/Kernel.php` file, you can specify a middleware for checking roles and permissions:

```php
protected $routeMiddleware = [
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'is' => \Kerigard\LaravelRoles\Middlewares\AuthorizeRole::class,
];
```

Then you can secure your routes:

```php
Route::put('users', [UserController::class, 'update'])->middleware('can:edit-users');
// or
Route::put('users', [UserController::class, 'update'])->can('edit-users');

Route::get('users', [UserController::class, 'index'])->middleware('is:admin');
// or
Route::get('users', [UserController::class, 'index'])->is('admin');
```

### Custom statuses

When throwing exceptions by default, Laravel returns a `403` error code with the message `This action is unauthorized`. You can specify your own error codes and messages for each role and permission:

```php
Role::create([
    'name' => 'Admin',
    'slug' => 'admin',
    'status' => 404,
    'message' => 'Not found',
]);
Permission::create([
    'name' => 'Edit users',
    'slug' => 'edit-users',
    'status' => 404,
    'message' => 'Not found',
]);
```

### Super admin

In the configuration, you can enable the super admin role. For users with this role, all permissions and role checks will be `true`.

## Change log

Please see the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

MIT. Please see the [LICENSE FILE](LICENSE.md) for more information.
