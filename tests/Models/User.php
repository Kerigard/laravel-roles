<?php

namespace Kerigard\LaravelRoles\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Kerigard\LaravelRoles\Traits\HasPermissions;
use Kerigard\LaravelRoles\Traits\HasRoles;

class User extends Authenticatable
{
    use HasPermissions;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Create model with fake attributes.
     *
     * @param  array  $attributes
     * @return \Kerigard\LaravelRoles\Tests\Models\User
     */
    public static function fake($attributes = []): User
    {
        return static::query()->create(array_merge([
            'email' => fake()->unique()->safeEmail(),
        ], $attributes));
    }
}
