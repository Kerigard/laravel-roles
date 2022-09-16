<?php

namespace Kerigard\LaravelRoles\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate;
use Kerigard\LaravelRoles\Middlewares\AuthorizeRole;
use Kerigard\LaravelRoles\Tests\Models\Role;
use Kerigard\LaravelRoles\Tests\Models\User;

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

        $user->detachAllRoles();
        $user->attachRole($role1);
        $user->syncRolesWithoutDetaching($role2);

        $this->assertTrue($user->hasRole(['role-1', 'role-2']));

        $user->detachRole($role1);

        $this->assertInstanceOf(Response::class, app(Gate::class)->authorizeRole($role2));

        $this->expectException(AuthorizationException::class);
        app(Gate::class)->authorizeRole($role1);
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

    public function test_attach_and_detach_roles()
    {
        $user = $this->createUser();
        $role1 = Role::fake(['slug' => 'role-1']);
        Role::fake(['slug' => 'role-2']);
        Role::fake(['slug' => 'role-3']);

        $user->attachRole($role1);
        $user->attachRole(3);
        $user->attachRole('role-3');

        $this->assertTrue($user->hasRole(['role-1', 'role-2', 'role-3']));

        $user->detachRole($role1);
        $user->detachRole(3);
        $user->detachRole('role-3');

        $this->assertFalse($user->hasAnyRole(['role-1', 'role-2', 'role-3']));

        $user->attachRole([$role1, 3, 'role-3']);

        $this->assertTrue($user->hasRole(['role-1', 'role-2', 'role-3']));

        $user->detachRole([$role1, 3, 'role-3']);

        $this->assertFalse($user->hasAnyRole(['role-1', 'role-2', 'role-3']));

        $user->syncRoles([$role1, 3, 'role-3']);

        $this->assertTrue($user->hasRole(['role-1', 'role-2', 'role-3']));

        $user->detachAllRoles();

        $this->assertFalse($user->hasAnyRole(['role-1', 'role-2', 'role-3']));
    }

    public function test_user_does_not_has_role()
    {
        $user = $this->createUser();
        $role1 = Role::fake(['slug' => 'role-1']);
        Role::fake(['slug' => 'role-2']);

        $this->assertTrue($user->doesNotHasAnyRole(['role-1', 'role-2']));

        $user->attachRole($role1);

        $this->assertFalse($user->doesNotHasAnyRole(['role-1', 'role-2']));
        $this->assertFalse($user->doesNotHasRole('role-1'));
        $this->assertTrue($user->doesNotHasRole('role-2'));
    }

    public function test_save_roles_for_new_user()
    {
        $user = new User(['email' => fake()->unique()->safeEmail()]);
        $role1 = Role::fake(['slug' => 'role-1']);

        $user->attachRole($role1);

        $this->assertTrue($user->doesNotHasRole($role1));

        $user->save();

        $this->assertTrue($user->hasRole($role1));
    }
}
