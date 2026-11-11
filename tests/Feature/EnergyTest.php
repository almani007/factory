<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnergyTest extends TestCase
{
    /** @test */
    public function user_can_view_energy_dashboard()
    {
        $response = $this->get('/energy');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Energy/Dashboard')
        );
    }

    /** @test */
    public function user_can_view_energy_consumption()
    {
        $response = $this->get('/energy/consumption');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Energy/Consumption')
        );
    }

    /** @test */
    public function user_can_view_energy_costs()
    {
        $response = $this->get('/energy/costs');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Energy/Costs')
        );
    }
}
