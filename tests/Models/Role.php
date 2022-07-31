<?php

namespace Kerigard\LaravelRoles\Tests\Models;

use Kerigard\LaravelRoles\Models\Role as BaseRole;

class Role extends BaseRole
{
    /**
     * Create model with fake attributes.
     *
     * @param  array  $attributes
     * @return \Kerigard\LaravelRoles\Tests\Models\Role
     */
    public static function fake($attributes = []): Role
    {
        return static::query()->create(array_merge([
            'name' => fake()->text(10),
        ], $attributes));
    }
}
