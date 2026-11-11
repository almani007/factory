<?php

namespace Tests\Feature;

use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_inventory()
    {
        $response = $this->get('/inventory');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Inventory/Inventory')
        );
    }

    /** @test */
    public function user_can_view_stock_transfers()
    {
        $response = $this->get('/inventory/transfers');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Inventory/StockTransfers')
        );
    }

    /** @test */
    public function user_can_view_stock_batches()
    {
        $response = $this->get('/inventory/batches');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Inventory/Batches')
        );
    }

    /** @test */
    public function user_can_view_stock_serials()
    {
        $response = $this->get('/inventory/serials');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Inventory/SerialNumbers')
        );
    }

    /** @test */
    public function user_can_view_stock_cross_docking()
    {
        $response = $this->get('/inventory/cross-docking');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Inventory/CrossDocking')
        );
    }

    /** @test */
    public function user_can_view_inventory_kitting()
    {
        $response = $this->get('/inventory/kitting');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Inventory/Kitting')
        );
    }

    /** @test */
    public function user_can_view_inventory_consignment()
    {
        $response = $this->get('/inventory/consignment');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Inventory/Consignment')
        );
    }
}
