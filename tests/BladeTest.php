<?php

namespace Kerigard\LaravelRoles\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Kerigard\LaravelRoles\Tests\Models\Role;

class BladeTest extends TestCase
{
    use InteractsWithViews;

    public function test_blade_directives()
    {
        $role1 = Role::fake(['slug' => 'role-1']);
        $role2 = Role::fake(['slug' => 'role-2']);

        $this->view('is', compact('role1', 'role2'))->assertSeeText('user does not have role');
        $this->view('isany', compact('role1', 'role2'))->assertSeeText('user does not have role');

        $user = $this->createUser();
        $user->attachRole($role2);

        $this->view('is', compact('role1', 'role2'))->assertSeeText("user has {$role2->slug}");
        $this->view('isany', compact('role1', 'role2'))->assertSeeText("user has role-3 or {$role2->slug}");

        $user->attachRole($role1);

        $this->view('is', compact('role1', 'role2'))->assertSeeText("user has {$role1->slug}");

        $user->detachRole($role2);

        $this->view('isany', compact('role1', 'role2'))->assertSeeText("user has role-3 or {$role1->slug}");
    }
}
