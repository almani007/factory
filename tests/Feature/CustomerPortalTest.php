<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    /** @test */
    public function user_can_view_customer_dashboard()
    {
        $response = $this->get('/customer/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('CustomerPortal/Dashboard')
        );
    }

    /** @test */
    public function user_can_view_customer_orders()
    {
        $response = $this->get('/customer/orders');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('CustomerPortal/Orders')
        );
    }

    /** @test */
    public function user_can_view_customer_invoices()
    {
        $response = $this->get('/customer/invoices');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('CustomerPortal/Invoices')
        );
    }

    /** @test */
    public function user_can_view_customer_quotes()
    {
        $response = $this->get('/customer/quotes');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('CustomerPortal/Quotes')
        );
    }
}
