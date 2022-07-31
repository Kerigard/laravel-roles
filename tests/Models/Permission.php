<?php

namespace Kerigard\LaravelRoles\Tests\Models;

use Kerigard\LaravelRoles\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    /**
     * Create model with fake attributes.
     *
     * @param  array  $attributes
     * @return \Kerigard\LaravelRoles\Tests\Models\Permission
     */
    public static function fake($attributes = []): Permission
    {
        return static::query()->create(array_merge([
            'name' => fake()->text(10),
        ], $attributes));
    }
}
