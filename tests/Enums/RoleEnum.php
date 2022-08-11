<?php

namespace Kerigard\LaravelRoles\Tests\Enums;

enum RoleEnum: int
{
    case SUPER_ADMIN = 1;
    case ADMIN = 2;
    case MANAGER = 3;
}
