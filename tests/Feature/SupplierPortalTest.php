<?php

namespace Tests\Feature;

use Tests\TestCase;

class SupplierPortalTest extends TestCase
{
    /** @test */
    public function user_can_view_supplier_dashboard()
    {
        $response = $this->get('/supplier/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('SupplierPortal/Dashboard')
        );
    }

    /** @test */
    public function user_can_view_supplier_purchase_orders()
    {
        $response = $this->get('/supplier/purchase-orders');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('SupplierPortal/PurchaseOrders')
        );
    }

    /** @test */
    public function user_can_view_supplier_invoices()
    {
        $response = $this->get('/supplier/invoices');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('SupplierPortal/Invoices')
        );
    }
}
