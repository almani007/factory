<?php

namespace Tests\Feature;

use Tests\TestCase;

class ComplianceTest extends TestCase
{
    /** @test */
    public function user_can_view_audit_trail()
    {
        $response = $this->get('/compliance/audit-trail');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Compliance/AuditTrail')
        );
    }
}
