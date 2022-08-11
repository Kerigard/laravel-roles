<?php

namespace Kerigard\LaravelRoles\Tests\Enums;

enum PermissionEnum: int
{
    case EDIT_ARTICLES = 1;
    case SHOW_ARTICLES = 2;
    case EDIT_USERS = 3;
}
