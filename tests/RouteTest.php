<?php

namespace Kerigard\LaravelRoles\Tests;

class RouteTest extends TestCase
{
    public function test_route_has_middlewares()
    {
        $middlewares = app('router')->get('test-route')->can('test-1')->is('role-1')->middleware();

        $this->assertEquals(['can:test-1', 'is:role-1'], $middlewares);
    }
}
