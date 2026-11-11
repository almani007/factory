<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReportingTest extends TestCase
{
    /** @test */
    public function user_can_view_reports()
    {
        $response = $this->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Reporting/Reports')
        );
    }

    /** @test */
    public function user_can_view_custom_reports()
    {
        $response = $this->get('/reports/custom');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Reports/CustomReports')
        );
    }

    /** @test */
    public function user_can_view_analytics()
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Analytics/Analytics')
        );
    }

    /** @test */
    public function user_can_view_advanced_analytics()
    {
        $response = $this->get('/analytics/advanced');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Analytics/Advanced')
        );
    }
}
