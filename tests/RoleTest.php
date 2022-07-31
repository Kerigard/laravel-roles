<?php

namespace Kerigard\LaravelRoles\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Kerigard\LaravelRoles\Middlewares\AuthorizeRole;
use Kerigard\LaravelRoles\Tests\Models\Role;

class RoleTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        if ($this->getName() == 'test_user_is_super_admin_defer') {
            $this->superAdminIsDefer = true;
        }

        parent::setUp();
    }

    public function test_role_creation()
    {
        $role = Role::create([
            'name' => 'Test role',
            'slug' => 'test-role',
            'status' => 404,
            'message' => 'Not found',
        ]);

        $this->assertModelExists($role);
        $this->assertEquals('Test role', $role->name);
        $this->assertEquals('test-role', $role->slug);
        $this->assertEquals(404, $role->status);
        $this->assertEquals('Not found', $role->message);
    }

    public function test_user_has_role()
    {
        $user = $this->createUser();
        $role1 = Role::fake(['slug' => 'role-1']);
        $role2 = Role::fake(['slug' => 'role-2']);

        $this->assertFalse($user->hasRole('role-1'));

        $user->attachRole($role1);

        $this->assertTrue($user->hasRole('role-1'));
        $this->assertTrue($user->hasRole(2));
        $this->assertTrue($user->hasRole($role1));

        $this->assertFalse($user->hasRole(['role-1', 'role-2']));
        $this->assertTrue($user->hasAnyRole(['role-1', 'role-2']));

        $user->attachRole($role2);

        $this->assertTrue($user->hasRole(['role-1', 'role-2']));
        $this->assertTrue($user->hasRole([2, $role2]));

        $user->detachRole($role1);

        $this->assertFalse($user->hasRole('role-1'));

        $user->detachAllRoles();

        $this->assertFalse($user->hasAnyRole(['role-1', 'role-2']));

        $user->syncRoles([1, 2]);

        $this->assertTrue($user->hasRole(collect(['role-1', 'role-2'])));
    }

    public function test_middleware_has_role_and_status_with_message()
    {
        $role = Role::fake(['slug' => 'role-1', 'status' => 404, 'message' => 'Not Found']);

        $middleware = $this->runMiddleware(AuthorizeRole::class, 'role-1');
        $this->assertInstanceOf(AuthorizationException::class, $middleware);
        $this->assertEquals($role->status, $middleware->status());
        $this->assertEquals($role->message, $middleware->getMessage());

        $user = $this->createUser();

        $middleware = $this->runMiddleware(AuthorizeRole::class, 'role-1');
        $this->assertInstanceOf(AuthorizationException::class, $middleware);
        $this->assertEquals($role->status, $middleware->status());
        $this->assertEquals($role->message, $middleware->getMessage());

        $user->attachRole($role);

        $this->assertEquals(200, $this->runMiddleware(AuthorizeRole::class, 'role-1')->getStatusCode());
    }
}
