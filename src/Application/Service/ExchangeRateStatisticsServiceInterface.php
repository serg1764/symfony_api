<?php

namespace App\Application\Service;

interface ExchangeRateStatisticsServiceInterface
{
    /**
     * Получить статистику по паре валют
     */
    public function getStatistics(string $baseCurrency, string $quoteCurrency): array;
} 