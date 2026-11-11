<?php

namespace Tests\Feature;

use Tests\TestCase;

class MaintenanceControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_maintenance_page()
    {
        $response = $this->get('/maintenance');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Maintenance/Maintenance')
        );
    }

    /** @test */
    public function user_can_view_preventive_maintenance()
    {
        $response = $this->get('/maintenance/preventive');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Maintenance/Preventive')
        );
    }

    /** @test */
    public function user_can_view_tools()
    {
        $response = $this->get('/maintenance/tools');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Maintenance/Tools')
        );
    }

    /** @test */
    public function user_can_view_calibration()
    {
        $response = $this->get('/maintenance/calibration');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Maintenance/Calibration')
        );
    }
}
