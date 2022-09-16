<?php

namespace Kerigard\LaravelRoles\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate;
use Kerigard\LaravelRoles\Tests\Enums\PermissionEnum;
use Kerigard\LaravelRoles\Tests\Enums\PermissionSlugEnum;
use Kerigard\LaravelRoles\Tests\Enums\RoleEnum;
use Kerigard\LaravelRoles\Tests\Enums\RoleSlugEnum;
use Kerigard\LaravelRoles\Tests\Models\Permission;
use Kerigard\LaravelRoles\Tests\Models\Role;

/**
 * @requires PHP >= 8.1
 */
class EnumTest extends TestCase
{
    public function test_enums_in_roles()
    {
        $user = $this->createUser();
        Role::fake(['slug' => RoleSlugEnum::ADMIN->value]);
        Role::fake(['slug' => RoleSlugEnum::MANAGER->value]);

        $user->attachRole([RoleEnum::ADMIN, RoleSlugEnum::MANAGER]);

        $this->assertTrue($user->hasRole([RoleEnum::ADMIN, RoleSlugEnum::MANAGER]));
        $this->assertTrue($user->doesNotHasRole(RoleEnum::SUPER_ADMIN));

        $this->assertInstanceOf(Response::class, app(Gate::class)->authorizeRole(RoleSlugEnum::MANAGER));

        $this->expectException(AuthorizationException::class);
        app(Gate::class)->authorizeRole(RoleSlugEnum::SUPER_ADMIN);
    }

    public function test_enums_in_permissions()
    {
        $user = $this->createUser();
        Permission::fake(['slug' => PermissionSlugEnum::EDIT_ARTICLES->value]);
        Permission::fake(['slug' => PermissionSlugEnum::SHOW_ARTICLES->value]);
        Permission::fake(['slug' => PermissionSlugEnum::EDIT_USERS->value]);

        $user->attachPermission([PermissionEnum::EDIT_ARTICLES, PermissionSlugEnum::SHOW_ARTICLES]);

        $this->assertTrue($user->hasPermission([PermissionEnum::EDIT_ARTICLES, PermissionSlugEnum::SHOW_ARTICLES]));
        $this->assertTrue($user->doesNotHasPermission(PermissionEnum::EDIT_USERS));

        $this->assertInstanceOf(Response::class, app(Gate::class)->authorize(PermissionEnum::SHOW_ARTICLES));

        $this->expectException(AuthorizationException::class);
        app(Gate::class)->authorize(PermissionEnum::EDIT_USERS);
    }
}
