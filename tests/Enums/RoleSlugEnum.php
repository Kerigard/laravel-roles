<?php

namespace Kerigard\LaravelRoles\Tests\Enums;

enum RoleSlugEnum: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
}
