<?php

namespace Kerigard\LaravelRoles\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Kerigard\LaravelRoles\Contracts\Permission;

trait HasPermissions
{
    /**
     * Get all of the user permissions for the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(config('roles.models.permission'), 'permissionable');
    }

    /**
     * Get all of the role permissions for the model.
     *
     * @return \Illuminate\Support\Collection
     */
    public function rolePermissions(): Collection
    {
        return $this->roles?->loadMissing('permissions')->pluck('permissions')->flatten() ?? collect();
    }

    /**
     * Get all of the permissions for the model.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allPermissions(): Collection
    {
        return $this->permissions->merge($this->rolePermissions());
    }

    /**
     * Determine if the model can fulfill all specified permissions.
     *
     * @param  string|int|iterable|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function checkPermission($permissions): bool
    {
        if (is_null($permissions)) {
            return true;
        }

        if (is_string($permissions)) {
            return $this->allPermissions()->contains('slug', $permissions);
        }

        if (is_int($permissions)) {
            return $this->allPermissions()->contains(app(Permission::class)->getKeyName(), $permissions);
        }

        if ($permissions instanceof Permission) {
            return $this->allPermissions()->contains($permissions->getKeyName(), $permissions->getKey());
        }

        if (! is_iterable($permissions)) {
            return false;
        }

        return collect($permissions)->every(fn ($permission) => $this->hasPermission($permission));
    }

    /**
     * Determine if the model can fulfill all specified permissions.
     *
     * @param  string|int|iterable|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function hasPermission($permissions): bool
    {
        if (config('roles.super_admin.enabled') && $this->roles?->contains('slug', config('roles.super_admin.slug'))) {
            return true;
        }

        return $this->checkPermission($permissions);
    }

    /**
     * Determine if the model can do any of the specified permissions.
     *
     * @param  string|int|iterable|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function hasAnyPermission($permissions): bool
    {
        return collect($permissions)->contains(fn ($permission) => $this->hasPermission($permission));
    }

    /**
     * Attach permission to a model.
     *
     * @param  int|\Kerigard\LaravelRoles\Contracts\Permission  $permission
     * @return void
     */
    public function attachPermission($permission): void
    {
        $this->permissions()->attach($permission);
        $this->load('permissions');
    }

    /**
     * Detach permission from a model.
     *
     * @param  int|\Kerigard\LaravelRoles\Contracts\Permission  $permission
     * @return void
     */
    public function detachPermission($permission): void
    {
        $this->permissions()->detach($permission);
        $this->load('permissions');
    }

    /**
     * Detach all permissions from a model.
     *
     * @return void
     */
    public function detachAllPermissions(): void
    {
        $this->permissions()->detach();
        $this->load('permissions');
    }

    /**
     * Sync permissions for a model.
     *
     * @param  \Illuminate\Support\Collection|\Kerigard\LaravelRoles\Contracts\Permission|array  $permissions
     * @return void
     */
    public function syncPermissions($permissions): void
    {
        $this->permissions()->sync($permissions);
        $this->load('permissions');
    }
}
