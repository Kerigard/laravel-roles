<?php

namespace Kerigard\LaravelRoles\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Kerigard\LaravelRoles\Contracts\Permission;
use UnitEnum;

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
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function checkPermission($permissions): bool
    {
        if (is_null($permissions)) {
            return true;
        }

        if ($permissions instanceof UnitEnum) {
            $permissions = $permissions->value;
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
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
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
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function hasAnyPermission($permissions): bool
    {
        return collect($permissions)->contains(fn ($permission) => $this->hasPermission($permission));
    }

    /**
     * Determine if the model may not comply with all specified permissions.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function doesNotHasPermission($permissions): bool
    {
        return ! $this->hasPermission($permissions);
    }

    /**
     * Determine if the model can fail any of the specified permissions.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return bool
     */
    public function doesNotHasAnyPermission($permissions): bool
    {
        return ! $this->hasAnyPermission($permissions);
    }

    /**
     * Attach permissions to a model.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return $this
     */
    public function attachPermission($permissions)
    {
        $this->savePermissions(
            fn (?Collection $permissions) => $this->permissions()->attach($permissions),
            $permissions
        );

        return $this;
    }

    /**
     * Detach permissions from a model.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return $this
     */
    public function detachPermission($permissions)
    {
        $this->savePermissions(
            fn (?Collection $permissions) => $this->permissions()->detach($permissions),
            $permissions ?? []
        );

        return $this;
    }

    /**
     * Detach all permissions from a model.
     *
     * @return $this
     */
    public function detachAllPermissions()
    {
        $this->savePermissions(fn () => $this->permissions()->detach());

        return $this;
    }

    /**
     * Sync permissions for a model.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @param  bool  $detaching
     * @return $this
     */
    public function syncPermissions($permissions, bool $detaching = true)
    {
        $this->savePermissions(
            fn (?Collection $permissions) => $this->permissions()->sync($permissions, $detaching),
            $permissions
        );

        return $this;
    }

    /**
     * Sync permissions for a model without detaching.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return $this
     */
    public function syncPermissionsWithoutDetaching($permissions)
    {
        return $this->syncPermissions($permissions, false);
    }

    /**
     * Save permissions for a model.
     *
     * @param  callable  $callback
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return void
     */
    protected function savePermissions(callable $callback, $permissions = null): void
    {
        $model = $this->getModel();

        if ($model->exists) {
            $callback($this->preparePermissions($permissions));
            $this->load('permissions');
        } else {
            $model->saved(function ($object) use ($callback, $permissions, $model) {
                if ($object->getKey() != $model->getKey()) {
                    return;
                }

                $callback($this->preparePermissions($permissions));
                $this->load('permissions');
            });
        }
    }

    /**
     * Prepare permissions before saving.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Permission|null  $permissions
     * @return \Illuminate\Support\Collection|null
     */
    protected function preparePermissions($permissions): ?Collection
    {
        if (is_null($permissions)) {
            return null;
        }

        $permissions = collect([$permissions])
            ->flatten()
            ->filter()
            ->transform(fn ($permission) => $permission instanceof UnitEnum ? $permission->value : $permission);
        $stringPermissions = $permissions->filter(fn ($permission) => is_string($permission));

        return $permissions
            ->filter(fn ($permission) => ! is_string($permission))
            ->when($stringPermissions->isNotEmpty(), fn (Collection $collection) => $collection->merge(
                app(Permission::class)->whereIn('slug', $stringPermissions)->pluck(app(Permission::class)->getKeyName())
            ))
            ->transform(fn ($permission) => is_int($permission) ? $permission : $permission->getKey());
    }
}
