<?php

namespace Kerigard\LaravelRoles\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Permission
{
    /**
     * Get all of the roles for the permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany;

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName();

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey();
}
