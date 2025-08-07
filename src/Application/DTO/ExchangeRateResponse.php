<?php

namespace App\Application\DTO;

/**
 * DTO для ответа с обменным курсом
 */
class ExchangeRateResponse
{
    public function __construct(
        private string $baseCurrency,
        private string $quoteCurrency,
        private float $rate,
        private \DateTimeImmutable $timestamp,
        private ?string $source = null
    ) {}

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function getQuoteCurrency(): string
    {
        return $this->quoteCurrency;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function toArray(): array
    {
        return [
            'base_currency' => $this->baseCurrency,
            'quote_currency' => $this->quoteCurrency,
            'rate' => $this->rate,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'source' => $this->source
        ];
    }
} 