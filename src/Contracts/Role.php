<?php

namespace Kerigard\LaravelRoles\Contracts;

interface Role
{
    /**
     * Get all of the permissions for the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions();

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
