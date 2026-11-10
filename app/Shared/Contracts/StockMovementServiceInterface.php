<?php

namespace App\Shared\Contracts;

interface StockMovementServiceInterface
{
    public function postMovement(array $dto): \App\Modules\Inventory\Models\StockMovement;
    public function reserveStock(string $itemId, int $warehouseId, float $quantity): \App\Modules\Inventory\Models\StockReservation;
    public function releaseReservation(string $reservationId): void;
    public function getAvailableQuantity(string $itemId, int $warehouseId): float;
}