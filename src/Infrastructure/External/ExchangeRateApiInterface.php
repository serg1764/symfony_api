<?php

namespace App\Infrastructure\External;

use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;

/**
 * Интерфейс для внешнего API обменных курсов
 */
interface ExchangeRateApiInterface
{
    /**
     * Получить текущий обменный курс для пары валют
     */
    public function getExchangeRate(Currency $baseCurrency, Currency $quoteCurrency): ExchangeRate;

    /**
     * Получить обменные курсы для нескольких пар валют
     */
    public function getExchangeRates(array $currencyPairs): array;

    /**
     * Проверить доступность API
     */
    public function isAvailable(): bool;
} 