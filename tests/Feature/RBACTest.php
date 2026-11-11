<?php

namespace Tests\Feature;

use Tests\TestCase;

class RBACControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_roles()
    {
        $response = $this->get('/rbac/roles');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('RBAC/Roles')
        );
    }

    /** @test */
    public function user_can_view_users()
    {
        $response = $this->get('/rbac/users');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('RBAC/Users')
        );
    }
}
