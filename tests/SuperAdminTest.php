<?php

namespace Kerigard\LaravelRoles\Tests;

use Illuminate\Contracts\Auth\Access\Gate;
use Kerigard\LaravelRoles\Tests\Models\Role;

class SuperAdminTest extends TestCase
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

    public function test_user_is_super_admin()
    {
        app(Gate::class)->define('permission-1', fn () => false);
        $user = $this->createUser();

        $this->assertFalse($user->hasPermission('permission-1'));
        $this->assertFalse($user->can('permission-1'));

        $user->attachRole(Role::whereSlug(config('roles.super_admin.slug'))->first());

        $this->assertTrue($user->hasPermission('permission-1'));
        $this->assertTrue($user->can('permission-1'));
        $this->assertTrue($user->hasRole('role-1'));
    }

    public function test_user_is_super_admin_defer()
    {
        app(Gate::class)->define('permission-1', fn () => false);
        $user = $this->createUser();
        $user->attachRole(Role::whereSlug(config('roles.super_admin.slug'))->first());

        $this->assertTrue($user->hasPermission('permission-1'));
        $this->assertFalse($user->can('permission-1'));
    }
}
