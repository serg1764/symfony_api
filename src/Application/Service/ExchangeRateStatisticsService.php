<?php

namespace App\Application\Service;

use App\Domain\Repository\ExchangeRateHistoryRepositoryInterface;

class ExchangeRateStatisticsService implements ExchangeRateStatisticsServiceInterface
{
    public function __construct(
        private ExchangeRateHistoryRepositoryInterface $repository
    ) {}

    /**
     * Получить статистику по паре валют
     */
    public function getStatistics(string $baseCurrency, string $quoteCurrency): array
    {
        // TODO: Реализовать получение реальной статистики из репозитория
        // Пока возвращаем заглушку
        return [
            'min_rate' => 100,
            'max_rate' => 888,
            'avg_rate' => 494,
            'total_records' => 500
        ];
    }
} 