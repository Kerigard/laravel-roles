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
     * Determine if the model may not comply with all specified permissions.
     *
     * @param  string|int|iterable|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function doesNotHasPermission($permissions): bool
    {
        return ! $this->hasPermission($permissions);
    }

    /**
     * Determine if the model can fail any of the specified permissions.
     *
     * @param  string|int|iterable|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function doesNotHasAnyPermission($permissions): bool
    {
        return ! $this->hasAnyPermission($permissions);
    }

    /**
     * Attach permissions to a model.
     *
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Permission  $permissions
     * @return void
     */
    public function attachPermission($permissions): void
    {
        $this->permissions()->attach($this->preparePermissions($permissions));
        $this->load('permissions');
    }

    /**
     * Detach permissions from a model.
     *
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Permission  $permissions
     * @return void
     */
    public function detachPermission($permissions): void
    {
        $this->permissions()->detach($this->preparePermissions($permissions));
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
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Permission  $permissions
     * @param  bool  $detaching
     * @return void
     */
    public function syncPermissions($permissions, bool $detaching = true): void
    {
        $this->permissions()->sync($this->preparePermissions($permissions), $detaching);
        $this->load('permissions');
    }

    /**
     * Sync permissions for a model without detaching.
     *
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Permission  $permissions
     * @return void
     */
    public function syncPermissionsWithoutDetaching($permissions): void
    {
        $this->syncPermissions($permissions, false);
    }

    /**
     * Prepare permissions before saving.
     *
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Permission  $permissions
     * @return \Illuminate\Support\Collection
     */
    private function preparePermissions($permissions): Collection
    {
        $permissions = collect([$permissions])->flatten()->filter();
        $stringPermissions = $permissions->filter(fn ($permission) => is_string($permission));

        return $permissions
            ->filter(fn ($permission) => ! is_string($permission))
            ->when($stringPermissions->isNotEmpty(), fn (Collection $collection) => $collection->merge(
                app(Permission::class)->whereIn('slug', $stringPermissions)->pluck(app(Permission::class)->getKeyName())
            ))
            ->transform(fn ($permission) => is_int($permission) ? $permission : $permission->getKey());
    }
}
