<?php

namespace App\Shared\Contracts;

interface MoneyInterface
{
    public function amount(): string;
    public function currency(): string;
    public function add(MoneyInterface $money): self;
    public function subtract(MoneyInterface $money): self;
    public function multiply(float $multiplier): self;
    public function isZero(): bool;
    public function isPositive(): bool;
    public function isNegative(): bool;
    public function __toString(): string;
}