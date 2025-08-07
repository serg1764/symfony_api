<?php

namespace App\Domain\Repository;

use App\Domain\Entity\CurrencyPair;
use App\Domain\ValueObject\Currency;

/**
 * Интерфейс репозитория для работы с парами валют
 */
interface CurrencyPairRepositoryInterface
{
    /**
     * Найти пару валют по ID
     */
    public function findById(int $id): ?CurrencyPair;

    /**
     * Найти пару валют по валютам
     */
    public function findByCurrencies(Currency $baseCurrency, Currency $quoteCurrency): ?CurrencyPair;

    /**
     * Найти все активные пары валют
     */
    public function findActivePairs(): array;

    /**
     * Сохранить пару валют
     */
    public function save(CurrencyPair $currencyPair): void;

    /**
     * Удалить пару валют
     */
    public function remove(CurrencyPair $currencyPair): void;

    /**
     * Проверить существование пары валют
     */
    public function exists(Currency $baseCurrency, Currency $quoteCurrency): bool;
} 