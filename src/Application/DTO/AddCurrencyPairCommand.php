<?php

namespace App\Application\DTO;

use App\Domain\ValueObject\Currency;

/**
 * DTO для команды добавления пары валют
 */
class AddCurrencyPairCommand
{
    public function __construct(
        private string $baseCurrency,
        private string $quoteCurrency
    ) {}

    public function getBaseCurrency(): Currency
    {
        return new Currency($this->baseCurrency);
    }

    public function getQuoteCurrency(): Currency
    {
        return new Currency($this->quoteCurrency);
    }
} 