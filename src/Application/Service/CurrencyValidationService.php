<?php

namespace App\Application\Service;

use App\Domain\ValueObject\Currency;
use InvalidArgumentException;

class CurrencyValidationService implements CurrencyValidationServiceInterface
{
    /**
     * Валидирует пару валют
     */
    public function validateCurrencyPair(string $baseCurrency, string $quoteCurrency): void
    {
        try {
            new Currency($baseCurrency);
            new Currency($quoteCurrency);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Неверная валюта: " . $e->getMessage());
        }
    }

    /**
     * Валидирует одну валюту
     */
    public function validateCurrency(string $currency): void
    {
        try {
            new Currency($currency);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Неверная валюта: " . $e->getMessage());
        }
    }
} 