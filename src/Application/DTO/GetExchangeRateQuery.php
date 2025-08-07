<?php

namespace App\Application\DTO;

use App\Domain\ValueObject\Currency;

/**
 * DTO для запроса получения обменного курса
 */
class GetExchangeRateQuery
{
    public function __construct(
        private string $baseCurrency,
        private string $quoteCurrency,
        private ?\DateTimeImmutable $date = null
    ) {}

    public function getBaseCurrency(): Currency
    {
        return new Currency($this->baseCurrency);
    }

    public function getQuoteCurrency(): Currency
    {
        return new Currency($this->quoteCurrency);
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }
} 