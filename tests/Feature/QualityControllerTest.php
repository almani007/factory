<?php

namespace Tests\Feature;

use Tests\TestCase;

class QualityControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_quality_page()
    {
        $response = $this->get('/quality');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Quality/Quality')
        );
    }

    /** @test */
    public function user_can_view_ncr_reports()
    {
        $response = $this->get('/quality/non-conformance');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Quality/NCR')
        );
    }

    /** @test */
    public function user_can_view_capa()
    {
        $response = $this->get('/quality/capa');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Quality/CAPA')
        );
    }

    /** @test */
    public function user_can_view_spc()
    {
        $response = $this->get('/quality/spc');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Quality/SPC')
        );
    }
}
