<?php

namespace Shared\Quantity;

use App\Shared\Contracts\QuantityInterface;

class Quantity implements QuantityInterface
{
    public function __construct(
        protected string $value,
        protected string $unit
    ) {}

    public function value(): string
    {
        return $this->value;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function add(QuantityInterface $quantity): self
    {
        if ($quantity->unit() !== $this->unit) {
            throw new \InvalidArgumentException('Unit mismatch');
        }
        return new self(bcadd($this->value, $quantity->value(), 4), $this->unit);
    }

    public function subtract(QuantityInterface $quantity): self
    {
        if ($quantity->unit() !== $this->unit) {
            throw new \InvalidArgumentException('Unit mismatch');
        }
        return new self(bcsub($this->value, $quantity->value(), 4), $this->unit);
    }

    public function multiply(float $multiplier): self
    {
        return new self(bcmul($this->value, (string) $multiplier, 4), $this->unit);
    }

    public function isZero(): bool
    {
        return bccomp($this->value, '0', 4) === 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->value, '0', 4) > 0;
    }

    public function equals(QuantityInterface $quantity): bool
    {
        return $this->unit === $quantity->unit()
            && bccomp($this->value, $quantity->value(), 4) === 0;
    }

    public function format(int $decimals = 2): string
    {
        return number_format((float) $this->value, $decimals) . ' ' . $this->unit;
    }

    public function __toString(): string
    {
        return $this->value . ' ' . $this->unit;
    }
}