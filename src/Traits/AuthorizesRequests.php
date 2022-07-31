<?php

namespace Kerigard\LaravelRoles\Traits;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests as BaseAuthorizesRequests;

trait AuthorizesRequests
{
    use BaseAuthorizesRequests;

    /**
     * Determine if a role exists for the current user.
     *
     * @param  string  $role
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeRole($role)
    {
        return app(Gate::class)->authorizeRole($role);
    }

    /**
     * Determine if a user has a role.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|mixed  $user
     * @param  string  $role
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeRoleForUser($user, $role)
    {
        return app(Gate::class)->forUser($user)->authorizeRole($role);
    }
}
