<?php

namespace Tests\Feature;

use Tests\TestCase;

class EHSTest extends TestCase
{
    /** @test */
    public function user_can_view_ehs_dashboard()
    {
        $response = $this->get('/ehs');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('EHS/Dashboard')
        );
    }

    /** @test */
    public function user_can_view_incidents()
    {
        $response = $this->get('/ehs/incidents');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('EHS/Incidents')
        );
    }

    /** @test */
    public function user_can_view_near_misses()
    {
        $response = $this->get('/ehs/near-misses');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('EHS/NearMisses')
        );
    }

    /** @test */
    public function user_can_view_safety_audits()
    {
        $response = $this->get('/ehs/safety-audits');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('EHS/SafetyAudits')
        );
    }
}
