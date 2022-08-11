<?php

namespace Kerigard\LaravelRoles\Tests\Enums;

enum PermissionSlugEnum: string
{
    case EDIT_ARTICLES = 'edit-articles';
    case SHOW_ARTICLES = 'show-articles';
    case EDIT_USERS = 'edit-users';
}
