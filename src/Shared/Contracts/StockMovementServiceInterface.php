<?php

namespace Shared\Contracts;

interface StockMovementServiceInterface
{
    public function postMovement(array $dto): \Inventory\Models\StockMovement;
    public function reserveStock(string $itemId, int $warehouseId, float $quantity): \Inventory\Models\StockReservation;
    public function releaseReservation(string $reservationId): void;
    public function getAvailableQuantity(string $itemId, int $warehouseId): float;
}