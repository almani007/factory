<?php

namespace Shared\Contracts;

interface QuantityInterface
{
    public function value(): string;
    public function unit(): string;
    public function add(QuantityInterface $quantity): self;
    public function subtract(QuantityInterface $quantity): self;
    public function multiply(float $multiplier): self;
    public function __toString(): string;
}