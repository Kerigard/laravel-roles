<?php

namespace Kerigard\LaravelRoles\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
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
     * Determine if the model has all the specified roles.
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
     * Attach roles to a model.
     *
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Role  $roles
     * @return void
     */
    public function attachRole($roles): void
    {
        $this->roles()->attach($this->prepareRoles($roles));
        $this->load('roles');
    }

    /**
     * Detach roles from a model.
     *
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Role  $roles
     * @return void
     */
    public function detachRole($roles): void
    {
        $this->roles()->detach($this->prepareRoles($roles));
        $this->load('roles');
    }

    /**
     * Detach all roles from a model.
     *
     * @return void
     */
    public function detachAllRoles(): void
    {
        $this->roles()->detach();
        $this->load('roles');
    }

    /**
     * Sync roles for a model.
     *
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Role  $roles
     * @return void
     */
    public function syncRoles($roles): void
    {
        $this->roles()->sync($this->prepareRoles($roles));
        $this->load('roles');
    }

    /**
     * Prepare roles before saving.
     *
     * @param  \Illuminate\Support\Collection|array|int|string|\Kerigard\LaravelRoles\Contracts\Role  $roles
     * @return \Illuminate\Support\Collection
     */
    private function prepareRoles($roles): Collection
    {
        $roles = collect([$roles])->flatten()->filter();
        $stringRoles = $roles->filter(fn ($role) => is_string($role));

        return $roles
            ->filter(fn ($role) => ! is_string($role))
            ->when($stringRoles->isNotEmpty(), fn (Collection $collection) => $collection->merge(
                app(Role::class)->whereIn('slug', $stringRoles)->pluck(app(Role::class)->getKeyName())
            ))
            ->transform(fn ($role) => is_int($role) ? $role : $role->getKey());
    }
}
