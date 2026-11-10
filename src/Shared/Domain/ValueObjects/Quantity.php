<?php

namespace Shared\Domain\ValueObjects;

use Brick\Math\BigDecimal;

/**
 * Quantity value object - handles quantities with unit and decimal precision.
 */
class Quantity
{
    public function __construct(
        public string $value,
        public string $unit
    ) {}

    public static function fromFloat(float $value, string $unit): self
    {
        return new self((string) BigDecimal::of((string) $value), $unit);
    }

    public static function zero(string $unit): self
    {
        return new self('0', $unit);
    }

    public function add(self $quantity): self
    {
        if ($quantity->unit !== $this->unit) {
            throw new \InvalidArgumentException("Unit mismatch: {$this->unit} vs {$quantity->unit}");
        }

        return new self(
            bcadd($this->value, $quantity->value, 4),
            $this->unit
        );
    }

    public function subtract(self $quantity): self
    {
        if ($quantity->unit !== $this->unit) {
            throw new \InvalidArgumentException("Unit mismatch: {$this->unit} vs {$quantity->unit}");
        }

        return new self(
            bcsub($this->value, $quantity->value, 4),
            $this->unit
        );
    }

    public function multiply(float $multiplier): self
    {
        return new self(
            bcmul($this->value, (string) $multiplier, 4),
            $this->unit
        );
    }

    public function isZero(): bool
    {
        return bccomp($this->value, '0', 4) === 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->value, '0', 4) > 0;
    }

    public function equals(self $quantity): bool
    {
        return $this->unit === $quantity->unit
            && bccomp($this->value, $quantity->value, 4) === 0;
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