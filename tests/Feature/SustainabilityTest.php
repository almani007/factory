<?php

namespace Tests\Feature;

use Tests\TestCase;

class SustainabilityTest extends TestCase
{
    /** @test */
    public function user_can_view_sustainability_dashboard()
    {
        $response = $this->get('/sustainability');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Sustainability/Dashboard')
        );
    }

    /** @test */
    public function user_can_view_carbon_tracking()
    {
        $response = $this->get('/sustainability/carbon');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Sustainability/Carbon')
        );
    }

    /** @test */
    public function user_can_view_waste_tracking()
    {
        $response = $this->get('/sustainability/waste');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Sustainability/Waste')
        );
    }
}
