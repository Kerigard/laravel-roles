<?php

namespace Kerigard\LaravelRoles\Middlewares;

use Closure;
use Illuminate\Contracts\Auth\Access\Gate;

class AuthorizeRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle($request, Closure $next, $role)
    {
        app(Gate::class)->authorizeRole($role);

        return $next($request);
    }
}
