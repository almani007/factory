<?php

namespace Shared\Money;

use App\Shared\Contracts\MoneyInterface;

class Money implements MoneyInterface
{
    public function __construct(
        protected string $amount,
        protected string $currency = 'PKR'
    ) {}

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(MoneyInterface $money): self
    {
        if ($money->currency() !== $this->currency) {
            throw new \InvalidArgumentException('Currency mismatch');
        }
        return new self(bcadd($this->amount, $money->amount(), 4), $this->currency);
    }

    public function subtract(MoneyInterface $money): self
    {
        if ($money->currency() !== $this->currency) {
            throw new \InvalidArgumentException('Currency mismatch');
        }
        return new self(bcsub($this->amount, $money->amount(), 4), $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self(bcmul($this->amount, (string) $multiplier, 4), $this->currency);
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

    public function equals(MoneyInterface $money): bool
    {
        return $this->currency === $money->currency()
            && bccomp($this->amount, $money->amount(), 4) === 0;
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