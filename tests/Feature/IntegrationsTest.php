<?php

namespace Tests\Feature;

use Tests\TestCase;

class IntegrationsTest extends TestCase
{
    /** @test */
    public function user_can_view_webhooks()
    {
        $response = $this->get('/integrations/webhooks');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Integrations/Webhooks')
        );
    }
}
