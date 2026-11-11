<?php

namespace Tests\Feature;

use Tests\TestCase;

class WorkflowTest extends TestCase
{
    /** @test */
    public function user_can_view_approvals()
    {
        $response = $this->get('/workflow/approvals');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Workflow/Approvals')
        );
    }

    /** @test */
    public function user_can_view_workflow_rules()
    {
        $response = $this->get('/workflow/rules');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Workflow/Rules')
        );
    }
}
