<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | If you wish, you can replace the standard models with your own,
    | expanding their functionality. The Role and Permission models must
    | implement the `Kerigard\LaravelRoles\Contracts\Role` and
    | `Kerigard\LaravelRoles\Contracts\Permission` contracts.
    |
    | The user model is used by default when getting relationships
    | roleables and permissionables.
    |
    */

    'models' => [
        'role' => \Kerigard\LaravelRoles\Models\Role::class,
        'permission' => \Kerigard\LaravelRoles\Models\Permission::class,
        'user' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Slug Separator
    |--------------------------------------------------------------------------
    |
    | You can change the slug separator. When saving slugs, spaces will be
    | replaced with a delimiter.
    |
    */

    'separator' => '-',

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    |
    | If necessary, you can enable the super admin role. For users with this
    | role, the hasRole and hasPermission checks will always return true.
    |
    | Enable defer if you want the role check to be performed at the very end.
    | Useful when there are restrictions for all users.
    |
    */

    'super_admin' => [
        'enabled' => false,
        'slug' => 'super-admin',
        'defer' => false,
    ],

];
