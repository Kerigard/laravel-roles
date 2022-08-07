<?php

namespace Kerigard\LaravelRoles\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Middleware\Authorize;
use Kerigard\LaravelRoles\Tests\Models\Permission;
use Kerigard\LaravelRoles\Tests\Models\Role;

class PermissionTest extends TestCase
{
    public function test_permission_creation()
    {
        $permission = Permission::create([
            'name' => 'Test permission',
            'slug' => 'test-permission',
            'status' => 404,
            'message' => 'Not found',
        ]);

        $this->assertModelExists($permission);
        $this->assertEquals('Test permission', $permission->name);
        $this->assertEquals('test-permission', $permission->slug);
        $this->assertEquals(404, $permission->status);
        $this->assertEquals('Not found', $permission->message);
    }

    public function test_user_has_permission()
    {
        $user = $this->createUser();
        $permission1 = Permission::fake(['slug' => 'permission-1']);
        $permission2 = Permission::fake(['slug' => 'permission-2']);

        $this->assertFalse($user->hasPermission('permission-1'));
        $this->assertFalse($user->can('permission-1'));

        $user->attachPermission($permission1);

        $this->assertTrue($user->hasPermission('permission-1'));
        $this->assertTrue($user->hasPermission(1));
        $this->assertTrue($user->hasPermission($permission1));
        $this->assertTrue($user->can('permission-1'));

        $this->assertFalse($user->hasPermission(['permission-1', 'permission-2']));
        $this->assertFalse($user->can(['permission-1', 'permission-2']));

        $this->assertTrue($user->hasAnyPermission(['permission-1', 'permission-2']));
        $this->assertTrue($user->canAny(['permission-1', 'permission-2']));

        $user->attachPermission($permission2);

        $this->assertTrue($user->hasPermission(['permission-1', 'permission-2']));
        $this->assertTrue($user->hasPermission([1, $permission2]));
        $this->assertTrue($user->can(['permission-1', 'permission-2']));

        $user->detachPermission($permission1);

        $this->assertFalse($user->hasPermission('permission-1'));

        $user->detachAllPermissions();

        $this->assertFalse($user->hasAnyPermission(['permission-1', 'permission-2']));

        $user->syncPermissions([1, 2]);

        $this->assertTrue($user->hasPermission(collect(['permission-1', 'permission-2'])));
    }

    public function test_user_has_permission_via_role()
    {
        $user = $this->createUser();
        $role = Role::fake(['slug' => 'role-1']);
        $permission = Permission::fake(['slug' => 'permission-1']);

        $this->assertFalse($role->hasPermission('permission-1'));

        $role->attachPermission($permission);

        $this->assertTrue($role->hasPermission('permission-1'));
        $this->assertFalse($user->hasPermission('permission-1'));

        $user->attachRole($role);

        $this->assertTrue($user->hasPermission('permission-1'));
    }

    public function test_middleware_has_permission_and_status_with_message()
    {
        $permission = Permission::fake(['slug' => 'permission-1', 'status' => 404, 'message' => 'Not Found']);

        $middleware = $this->runMiddleware(Authorize::class, 'permission-1');
        $this->assertInstanceOf(AuthorizationException::class, $middleware);
        $this->assertEquals($permission->status, $middleware->status());
        $this->assertEquals($permission->message, $middleware->getMessage());

        $user = $this->createUser();

        $middleware = $this->runMiddleware(Authorize::class, 'permission-1');
        $this->assertInstanceOf(AuthorizationException::class, $middleware);
        $this->assertEquals($permission->status, $middleware->status());
        $this->assertEquals($permission->message, $middleware->getMessage());

        $user->attachPermission($permission);

        $this->assertEquals(200, $this->runMiddleware(Authorize::class, 'permission-1')->getStatusCode());
    }

    public function test_attach_and_detach_permissions()
    {
        $user = $this->createUser();
        $permission1 = Permission::fake(['slug' => 'permission-1']);
        Permission::fake(['slug' => 'permission-2']);
        Permission::fake(['slug' => 'permission-3']);

        $user->attachPermission($permission1);
        $user->attachPermission(2);
        $user->attachPermission('permission-3');

        $this->assertTrue($user->hasPermission(['permission-1', 'permission-2', 'permission-3']));

        $user->detachPermission($permission1);
        $user->detachPermission(2);
        $user->detachPermission('permission-3');

        $this->assertFalse($user->hasAnyPermission(['permission-1', 'permission-2', 'permission-3']));

        $user->attachPermission([$permission1, 2, 'permission-3']);

        $this->assertTrue($user->hasPermission(['permission-1', 'permission-2', 'permission-3']));

        $user->detachPermission([$permission1, 2, 'permission-3']);

        $this->assertFalse($user->hasAnyPermission(['permission-1', 'permission-2', 'permission-3']));

        $user->syncPermissions([$permission1, 2, 'permission-3']);

        $this->assertTrue($user->hasPermission(['permission-1', 'permission-2', 'permission-3']));

        $user->detachAllPermissions();

        $this->assertFalse($user->hasAnyPermission(['permission-1', 'permission-2', 'permission-3']));
    }

    public function test_user_does_not_has_permission()
    {
        $user = $this->createUser();
        $permission1 = Permission::fake(['slug' => 'permission-1']);
        Permission::fake(['slug' => 'permission-2']);

        $this->assertTrue($user->doesNotHasAnyPermission(['permission-1', 'permission-2']));

        $user->attachPermission($permission1);

        $this->assertFalse($user->doesNotHasAnyPermission(['permission-1', 'permission-2']));
        $this->assertFalse($user->doesNotHasPermission('permission-1'));
        $this->assertTrue($user->doesNotHasPermission('permission-2'));
    }
}
