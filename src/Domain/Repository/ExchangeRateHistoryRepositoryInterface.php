<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\ValueObject\Currency;

/**
 * Интерфейс репозитория для работы с историей обменных курсов
 */
interface ExchangeRateHistoryRepositoryInterface
{
    /**
     * Найти последний курс для пары валют
     */
    public function findLatestRate(Currency $baseCurrency, Currency $quoteCurrency): ?ExchangeRateRecord;

    /**
     * Найти курс на определенную дату
     */
    public function findRateAtDate(Currency $baseCurrency, Currency $quoteCurrency, \DateTimeImmutable $date): ?ExchangeRateRecord;

    /**
     * Найти историю курсов за период
     */
    public function findRatesInPeriod(
        Currency $baseCurrency,
        Currency $quoteCurrency,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to
    ): array;

    /**
     * Сохранить запись истории курса
     */
    public function save(ExchangeRateRecord $exchangeRateRecord): void;

    /**
     * Удалить старые записи (старше указанной даты)
     */
    public function deleteOldRecords(\DateTimeImmutable $olderThan): int;

    /**
     * Получить статистику по курсам
     */
    public function getStatistics(Currency $baseCurrency, Currency $quoteCurrency): array;
} 