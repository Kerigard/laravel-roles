<?php

namespace Kerigard\LaravelRoles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kerigard\LaravelRoles\Contracts\Permission as PermissionContract;
use Kerigard\LaravelRoles\Traits\Sluggable;

class Permission extends Model implements PermissionContract
{
    use Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
        'message',
    ];

    /**
     * Get all of the relations for the permission.
     *
     * @param  string|null  $model
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissionables(string $model = null): MorphToMany
    {
        return $this->morphedByMany($model ?? config('roles.models.user'), 'permissionable');
    }

    /**
     * Get all of the roles for the permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany
    {
        return $this->permissionables(config('roles.models.role'));
    }
}
