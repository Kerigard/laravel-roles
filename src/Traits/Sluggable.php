<?php

namespace Kerigard\LaravelRoles\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait Sluggable
{
    /**
     * Interact with the model slug.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => Str::slug($value, config('roles.separator')),
        );
    }
}
