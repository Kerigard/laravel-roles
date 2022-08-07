<?php

namespace Kerigard\LaravelRoles\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Role
{
    /**
     * Get all of the permissions for the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions(): MorphToMany;

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
