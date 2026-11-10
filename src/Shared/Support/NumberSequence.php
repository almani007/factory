<?php

namespace Shared\Support;

class NumberSequence
{
    protected string $prefix;
    protected int $padding;
    protected int $lastNumber;

    public function __construct(string $prefix, int $padding = 6, int $lastNumber = 0)
    {
        $this->prefix = $prefix;
        $this->padding = $padding;
        $this->lastNumber = $lastNumber;
    }

    public function next(): string
    {
        $this->lastNumber++;
        return $this->prefix.str_pad((string) $this->lastNumber, $this->padding, '0', STR_PAD_LEFT);
    }

    public function current(): string
    {
        return $this->prefix.str_pad((string) $this->lastNumber, $this->padding, '0', STR_PAD_LEFT);
    }
}