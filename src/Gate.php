<?php

namespace Kerigard\LaravelRoles;

use Illuminate\Auth\Access\Gate as BaseGate;
use Illuminate\Auth\Access\Response;
use Kerigard\LaravelRoles\Contracts\Permission;
use Kerigard\LaravelRoles\Contracts\Role;

class Gate extends BaseGate
{
    /**
     * Inspect the user for the specified permission.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return \Illuminate\Auth\Access\Response
     */
    public function inspectPermission($ability, $arguments = []): Response
    {
        $response = $this->inspect($ability, $arguments);

        if ($response->denied() && $permission = app(Permission::class)->whereSlug($ability)->first()) {
            $response = $response->denyWithStatus($permission->status, $permission->message);
        }

        return $response;
    }

    /**
     * Inspect the user for the specified role.
     *
     * @param  string  $role
     * @return \Illuminate\Auth\Access\Response
     */
    public function inspectRole($role): Response
    {
        $user = $this->resolveUser();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole($role)) {
            $response = Response::allow();
        } else {
            $role = app(Role::class)->whereSlug($role)->first();
            $response = Response::denyWithStatus($role?->status, $role?->message);
        }

        return $response;
    }

    /**
     * Determine if the given ability should be granted for the current user.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        return $this->inspectPermission($ability, $arguments)->authorize();
    }

    /**
     * Determine if the current user has a given role.
     *
     * @param  string  $role
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeRole($role)
    {
        return $this->inspectRole($role)->authorize();
    }
}
