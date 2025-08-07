<?php

namespace App\Application\Service;

interface CurrencyValidationServiceInterface
{
    /**
     * Валидирует пару валют
     */
    public function validateCurrencyPair(string $baseCurrency, string $quoteCurrency): void;

    /**
     * Валидирует одну валюту
     */
    public function validateCurrency(string $currency): void;
} 