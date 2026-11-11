<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_production_page()
    {
        $response = $this->get('/production');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Production/Production')
        );
    }

    /** @test */
    public function user_can_view_work_orders()
    {
        $response = $this->get('/production/work-orders');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Production/WorkOrders')
        );
    }

    /** @test */
    public function user_can_view_production_schedule()
    {
        $response = $this->get('/production/schedule');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Production/Schedule')
        );
    }

    /** @test */
    public function user_can_view_production_downtime()
    {
        $response = $this->get('/production/downtime');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Production/Downtime')
        );
    }

    /** @test */
    public function user_can_view_scrap_tracking()
    {
        $response = $this->get('/production/scrap');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Production/Scrap')
        );
    }
}
