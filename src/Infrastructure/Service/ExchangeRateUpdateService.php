<?php

namespace App\Infrastructure\Service;

use App\Domain\Entity\CurrencyPair;
use App\Domain\Entity\ExchangeRateHistory;
use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\Repository\CurrencyPairRepositoryInterface;
use App\Domain\Repository\ExchangeRateHistoryRepositoryInterface;
use App\Domain\Event\ExchangeRateUpdatedEvent;
use App\Infrastructure\External\ExchangeRateApiInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Service\DbLoggerInterface;

/**
 * Сервис для обновления обменных курсов
 */
class ExchangeRateUpdateService
{
    public function __construct(
        private CurrencyPairRepositoryInterface $currencyPairRepository,
        private ExchangeRateHistoryRepositoryInterface $exchangeRateHistoryRepository,
        private ExchangeRateApiInterface $exchangeRateApi,
        private EventDispatcherInterface $eventDispatcher,
        private DbLoggerInterface $dbLogger
    ) {}

    /**
     * Обновить курсы для всех активных пар валют
     */
    public function updateAllRates(): int
    {
        $activePairs = $this->currencyPairRepository->findActivePairs();
        $updatedCount = 0;

        foreach ($activePairs as $currencyPair) {
            try {
                $this->updateRateForPair($currencyPair);
                $updatedCount++;
            } catch (\Exception $e) {
                $this->dbLogger->log('exchange', 'error', 'Ошибка обновления курса для пары валют', [
                    'pair' => $currencyPair->getPairCode(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->dbLogger->log('exchange', 'info', 'Обновление курсов завершено', [
            'total_pairs' => count($activePairs),
            'updated_count' => $updatedCount
        ]);

        return $updatedCount;
    }

    /**
     * Обновить курс для конкретной пары валют
     */
    public function updateRateForPair(CurrencyPair $currencyPair): void
    {
        $baseCurrency = $currencyPair->getBaseCurrency();
        $quoteCurrency = $currencyPair->getQuoteCurrency();

        // Получаем текущий курс от внешнего API
        $exchangeRate = $this->exchangeRateApi->getExchangeRate($baseCurrency, $quoteCurrency);

        // Создаем запись в истории
        $exchangeRateHistory = new ExchangeRateHistory($baseCurrency, $quoteCurrency, $exchangeRate);
        $exchangeRateRecord = \App\Domain\Factory\ExchangeRateEntityFactory::createEntity($baseCurrency, $quoteCurrency, $exchangeRate);

        // Сохраняем в базу данных
        $this->exchangeRateHistoryRepository->save($exchangeRateRecord);

        // Диспатчим событие
        $this->eventDispatcher->dispatch(new ExchangeRateUpdatedEvent($exchangeRateHistory));

        $this->dbLogger->log('exchange', 'info', 'Курс обновлен', [
            'pair' => $currencyPair->getPairCode(),
            'rate' => $exchangeRate->getRate(),
            'timestamp' => $exchangeRate->getTimestamp()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Очистить старые записи истории
     */
    public function cleanupOldRecords(int $daysToKeep = 30): int
    {
        $cutoffDate = new \DateTimeImmutable("-{$daysToKeep} days");
        $deletedCount = $this->exchangeRateHistoryRepository->deleteOldRecords($cutoffDate);

        $this->dbLogger->log('exchange', 'info', 'Очистка старых записей завершена', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->format('Y-m-d')
        ]);

        return $deletedCount;
    }
} 