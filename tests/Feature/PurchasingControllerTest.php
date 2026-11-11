<?php

namespace Tests\Feature;

use Tests\TestCase;

class PurchasingControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_purchasing_page()
    {
        $response = $this->get('/purchasing');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Purchasing/Purchasing')
        );
    }

    /** @test */
    public function user_can_view_purchase_requisitions()
    {
        $response = $this->get('/supply-chain/purchase-requisitions');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('SupplyChain/PurchaseRequisitions')
        );
    }

    /** @test */
    public function user_can_view_supplier_invoices()
    {
        $response = $this->get('/purchasing/supplier-invoices');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Purchasing/SupplierInvoices')
        );
    }

    /** @test */
    public function user_can_view_vendor_scorecards()
    {
        $response = $this->get('/purchasing/vendor-scorecards');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Purchasing/VendorScorecards')
        );
    }
}
