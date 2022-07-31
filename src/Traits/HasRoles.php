<?php

namespace Kerigard\LaravelRoles\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kerigard\LaravelRoles\Contracts\Role;

trait HasRoles
{
    /**
     * Get all of the user roles for the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(config('roles.models.role'), 'roleable');
    }

    /**
     * Determine if the model has all the specified roles..
     *
     * @param  string|int|iterable|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (config('roles.super_admin.enabled') && $this->roles->contains('slug', config('roles.super_admin.slug'))) {
            return true;
        }

        if (is_null($roles)) {
            return true;
        }

        if (is_string($roles)) {
            return $this->roles->contains('slug', $roles);
        }

        if (is_int($roles)) {
            return $this->roles->contains(app(Role::class)->getKeyName(), $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains($roles->getKeyName(), $roles->getKey());
        }

        if (! is_iterable($roles)) {
            return false;
        }

        return collect($roles)->every(fn ($role) => $this->hasRole($role));
    }

    /**
     * Determine if the model has any of the specified roles.
     *
     * @param  string|int|iterable|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        return collect($roles)->contains(fn ($role) => $this->hasRole($role));
    }

    /**
     * Attach role to a user.
     *
     * @param  int|\Kerigard\LaravelRoles\Contracts\Role  $role
     * @return void
     */
    public function attachRole($role): void
    {
        $this->roles()->attach($role);
        $this->load('roles');
    }

    /**
     * Detach role from a user.
     *
     * @param  int|\Kerigard\LaravelRoles\Contracts\Role  $role
     * @return void
     */
    public function detachRole($role): void
    {
        $this->roles()->detach($role);
        $this->load('roles');
    }

    /**
     * Detach all roles from a user.
     *
     * @return void
     */
    public function detachAllRoles(): void
    {
        $this->roles()->detach();
        $this->load('roles');
    }

    /**
     * Sync roles for a user.
     *
     * @param  \Illuminate\Support\Collection|\Kerigard\LaravelRoles\Contracts\Role|array  $roles
     * @return void
     */
    public function syncRoles($roles): void
    {
        $this->roles()->sync($roles);
        $this->load('roles');
    }
}
