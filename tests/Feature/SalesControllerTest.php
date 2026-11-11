<?php

namespace Tests\Feature;

use Tests\TestCase;

class SalesControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_sales_page()
    {
        $response = $this->get('/sales');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Sales/Sales')
        );
    }

    /** @test */
    public function user_can_view_sales_quotes()
    {
        $response = $this->get('/sales/quotes');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Sales/Quotes')
        );
    }

    /** @test */
    public function user_can_view_customer_invoices()
    {
        $response = $this->get('/sales/invoices');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Sales/CustomerInvoices')
        );
    }

    /** @test */
    public function user_can_view_cpq()
    {
        $response = $this->get('/sales/cpq');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Sales/CPQ')
        );
    }

    /** @test */
    public function user_can_view_commissions()
    {
        $response = $this->get('/sales/commissions');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Sales/Commissions')
        );
    }
}
