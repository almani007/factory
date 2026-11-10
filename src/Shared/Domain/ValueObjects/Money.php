<?php

namespace Shared\Domain\ValueObjects;

use Brick\Math\BigDecimal;

/**
 * Money value object - handles monetary amounts with decimal precision.
 * Never uses floating-point for money.
 */
class Money
{
    public function __construct(
        public string $amount,
        public string $currency = 'PKR'
    ) {}

    public static function fromFloat(float $value, string $currency = 'PKR'): self
    {
        return new self((string) BigDecimal::of((string) $value), $currency);
    }

    public static function zero(string $currency = 'PKR'): self
    {
        return new self('0', $currency);
    }

    public function add(self $money): self
    {
        if ($money->currency !== $this->currency) {
            throw new \InvalidArgumentException("Currency mismatch: {$this->currency} vs {$money->currency}");
        }

        return new self(
            bcadd($this->amount, $money->amount, 4),
            $this->currency
        );
    }

    public function subtract(self $money): self
    {
        if ($money->currency !== $this->currency) {
            throw new \InvalidArgumentException("Currency mismatch: {$this->currency} vs {$money->currency}");
        }

        return new self(
            bcsub($this->amount, $money->amount, 4),
            $this->currency
        );
    }

    public function multiply(float $multiplier): self
    {
        return new self(
            bcmul($this->amount, (string) $multiplier, 4),
            $this->currency
        );
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', 4) === 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->amount, '0', 4) > 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', 4) < 0;
    }

    public function equals(self $money): bool
    {
        return $this->currency === $money->currency
            && bccomp($this->amount, $money->amount, 4) === 0;
    }

    public function format(int $decimals = 2): string
    {
        return number_format((float) $this->amount, $decimals) . ' ' . $this->currency;
    }

    public function __toString(): string
    {
        return $this->currency . ' ' . $this->amount;
    }
}