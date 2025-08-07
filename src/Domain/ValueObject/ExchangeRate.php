<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Value Object для представления обменного курса
 */
final class ExchangeRate implements \JsonSerializable
{
    private float $rate;
    private \DateTimeImmutable $timestamp;

    public function __construct(float $rate, ?\DateTimeImmutable $timestamp = null)
    {
        $this->validate($rate);
        $this->rate = $rate;
        $this->timestamp = $timestamp ?? new \DateTimeImmutable();
    }

    private function validate(float $rate): void
    {
        if ($rate <= 0) {
            throw new InvalidArgumentException('Обменный курс должен быть положительным числом');
        }

        if ($rate > 1000000) {
            throw new InvalidArgumentException('Обменный курс слишком высокий');
        }
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function equals(ExchangeRate $other): bool
    {
        return $this->rate === $other->rate && 
               $this->timestamp->getTimestamp() === $other->timestamp->getTimestamp();
    }

    public function __toString(): string
    {
        return (string) $this->rate;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'rate' => $this->rate,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s')
        ];
    }

    public static function fromFloat(float $rate, ?\DateTimeImmutable $timestamp = null): self
    {
        return new self($rate, $timestamp);
    }
} 