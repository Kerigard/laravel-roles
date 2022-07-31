<?php

namespace Kerigard\LaravelRoles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kerigard\LaravelRoles\Contracts\Role as RoleContract;
use Kerigard\LaravelRoles\Traits\HasPermissions;
use Kerigard\LaravelRoles\Traits\Sluggable;

class Role extends Model implements RoleContract
{
    use HasPermissions;
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
     * Get all of the relations for the role.
     *
     * @param  string|null  $model
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roleables(string $model = null): MorphToMany
    {
        return $this->morphedByMany($model ?? config('roles.models.user'), 'roleable');
    }
}
