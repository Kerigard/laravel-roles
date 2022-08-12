<?php

namespace Kerigard\LaravelRoles\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Kerigard\LaravelRoles\Contracts\Role;
use UnitEnum;

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
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
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

        if ($roles instanceof UnitEnum) {
            $roles = $roles->value;
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
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        return collect($roles)->contains(fn ($role) => $this->hasRole($role));
    }

    /**
     * Determine if the model does not has all the specified roles.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return bool
     */
    public function doesNotHasRole($roles): bool
    {
        return ! $this->hasRole($roles);
    }

    /**
     * Determine if the model does not has any of the specified roles.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return bool
     */
    public function doesNotHasAnyRole($roles): bool
    {
        return ! $this->hasAnyRole($roles);
    }

    /**
     * Attach roles to a model.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return $this
     */
    public function attachRole($roles)
    {
        $this->saveRoles(fn (?Collection $roles) => $this->roles()->attach($roles), $roles);

        return $this;
    }

    /**
     * Detach roles from a model.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return $this
     */
    public function detachRole($roles)
    {
        $this->saveRoles(fn (?Collection $roles) => $this->roles()->detach($roles), $roles ?? []);

        return $this;
    }

    /**
     * Detach all roles from a model.
     *
     * @return $this
     */
    public function detachAllRoles()
    {
        $this->saveRoles(fn () => $this->roles()->detach());

        return $this;
    }

    /**
     * Sync roles for a model.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @param  bool  $detaching
     * @return $this
     */
    public function syncRoles($roles, bool $detaching = true)
    {
        $this->saveRoles(fn (?Collection $roles) => $this->roles()->sync($roles, $detaching), $roles);

        return $this;
    }

    /**
     * Sync roles for a model without detaching.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return $this
     */
    public function syncRolesWithoutDetaching($roles)
    {
        return $this->syncRoles($roles, false);
    }

    /**
     * Save roles for a model.
     *
     * @param  callable  $callback
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return void
     */
    protected function saveRoles(callable $callback, $roles = null): void
    {
        $model = $this->getModel();

        if ($model->exists) {
            $callback($this->prepareRoles($roles));
            $this->load('roles');
        } else {
            $model->saved(function ($object) use ($callback, $roles, $model) {
                if ($object->getKey() != $model->getKey()) {
                    return;
                }

                $callback($this->prepareRoles($roles));
                $this->load('roles');
            });
        }
    }

    /**
     * Prepare roles before saving.
     *
     * @param  string|int|iterable|\UnitEnum|\Kerigard\LaravelRoles\Contracts\Role|null  $roles
     * @return \Illuminate\Support\Collection|null
     */
    protected function prepareRoles($roles): ?Collection
    {
        if (is_null($roles)) {
            return null;
        }

        $roles = collect([$roles])
            ->flatten()
            ->filter()
            ->transform(fn ($role) => $role instanceof UnitEnum ? $role->value : $role);
        $stringRoles = $roles->filter(fn ($role) => is_string($role));

        return $roles
            ->filter(fn ($role) => ! is_string($role))
            ->when($stringRoles->isNotEmpty(), fn (Collection $collection) => $collection->merge(
                app(Role::class)->whereIn('slug', $stringRoles)->pluck(app(Role::class)->getKeyName())
            ))
            ->transform(fn ($role) => is_int($role) ? $role : $role->getKey());
    }
}
