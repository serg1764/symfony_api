<?php

namespace App\Application\Handler;

use App\Application\DTO\GetExchangeRateQuery;
use App\Application\DTO\ExchangeRateResponse;
use App\Domain\Repository\ExchangeRateHistoryRepositoryInterface;
use App\Infrastructure\External\ExchangeRateApiInterface;
use Psr\Log\LoggerInterface;

/**
 * Обработчик запроса получения обменного курса
 */
class GetExchangeRateHandler
{
    public function __construct(
        private ExchangeRateHistoryRepositoryInterface $exchangeRateHistoryRepository,
        private ExchangeRateApiInterface $exchangeRateApi,
        private LoggerInterface $logger
    ) {}

    public function handle(GetExchangeRateQuery $query): ExchangeRateResponse
    {
        $baseCurrency = $query->getBaseCurrency();
        $quoteCurrency = $query->getQuoteCurrency();
        $date = $query->getDate();

        $this->logger->info('Запрос обменного курса', [
            'base_currency' => $baseCurrency->getCode(),
            'quote_currency' => $quoteCurrency->getCode(),
            'date' => $date?->format('Y-m-d H:i:s')
        ]);

        // Если указана дата, ищем исторический курс
        if ($date !== null) {
            $exchangeRateHistory = $this->exchangeRateHistoryRepository->findRateAtDate(
                $baseCurrency,
                $quoteCurrency,
                $date
            );

            if ($exchangeRateHistory) {
                $rate = $exchangeRateHistory->getRate();
                return new ExchangeRateResponse(
                    $baseCurrency->getCode(),
                    $quoteCurrency->getCode(),
                    $rate->getRate(),
                    $rate->getTimestamp(),
                    'historical'
                );
            }
        }

        // Иначе получаем последний курс из истории
        $latestRate = $this->exchangeRateHistoryRepository->findLatestRate($baseCurrency, $quoteCurrency);

        if ($latestRate) {
            $rate = $latestRate->getRate();
            return new ExchangeRateResponse(
                $baseCurrency->getCode(),
                $quoteCurrency->getCode(),
                $rate->getRate(),
                $rate->getTimestamp(),
                'historical'
            );
        }

        // Если нет в истории, получаем от внешнего API
        $exchangeRate = $this->exchangeRateApi->getExchangeRate($baseCurrency, $quoteCurrency);

        return new ExchangeRateResponse(
            $baseCurrency->getCode(),
            $quoteCurrency->getCode(),
            $exchangeRate->getRate(),
            $exchangeRate->getTimestamp(),
            'external_api'
        );
    }
} 