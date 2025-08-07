<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\Factory\ExchangeRateEntityFactory;
use App\Domain\Repository\ExchangeRateHistoryRepositoryInterface;
use App\Domain\ValueObject\Currency;
use App\Service\DbLoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Реализация репозитория для работы с историей обменных курсов
 */
class ExchangeRateHistoryRepository implements ExchangeRateHistoryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DbLoggerInterface $dbLogger
    ) {
    }

    public function findLatestRate(Currency $baseCurrency, Currency $quoteCurrency): ?ExchangeRateRecord
    {
        $entityClass = ExchangeRateEntityFactory::getEntityClass($baseCurrency, $quoteCurrency);
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e')
           ->from($entityClass, 'e')
           ->orderBy('e.timestamp', 'DESC')
           ->setMaxResults(1);

        $query = $qb->getQuery();
        
        // Логируем SQL запрос
        $this->dbLogger->log(
            'sql',
            'info',
            'Find latest rate query',
            [
                'sql' => $query->getSQL(),
                'parameters' => $query->getParameters(),
                'base_currency' => $baseCurrency->getCode(),
                'quote_currency' => $quoteCurrency->getCode(),
                'entity_class' => $entityClass
            ]
        );

        return $query->getOneOrNullResult();
    }

    public function findRateAtDate(Currency $baseCurrency, Currency $quoteCurrency, \DateTimeImmutable $date): ?ExchangeRateRecord
    {
        $entityClass = ExchangeRateEntityFactory::getEntityClass($baseCurrency, $quoteCurrency);
        
        // Создаем диапазон для поиска в пределах минуты
        $startOfMinute = $date->setTime((int)$date->format('H'), (int)$date->format('i'), 0);
        $endOfMinute = $startOfMinute->modify('+59 seconds');
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e')
           ->from($entityClass, 'e')
           ->where('e.timestamp >= :startOfMinute')
           ->andWhere('e.timestamp <= :endOfMinute')
           ->setParameter('startOfMinute', $startOfMinute)
           ->setParameter('endOfMinute', $endOfMinute)
           ->orderBy('e.timestamp', 'DESC')
           ->setMaxResults(1);

        $query = $qb->getQuery();
        
        // Логируем SQL запрос
        $this->dbLogger->log(
            'sql',
            'info',
            'Find rate at date query',
            [
                'sql' => $query->getSQL(),
                'parameters' => $query->getParameters(),
                'base_currency' => $baseCurrency->getCode(),
                'quote_currency' => $quoteCurrency->getCode(),
                'date' => $date->format('Y-m-d H:i:s'),
                'entity_class' => $entityClass
            ]
        );

        return $query->getOneOrNullResult();
    }

    public function findRatesInPeriod(
        Currency $baseCurrency,
        Currency $quoteCurrency,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to
    ): array {
        $entityClass = ExchangeRateEntityFactory::getEntityClass($baseCurrency, $quoteCurrency);
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e')
           ->from($entityClass, 'e')
           ->where('e.timestamp >= :from')
           ->andWhere('e.timestamp <= :to')
           ->setParameter('from', $from)
           ->setParameter('to', $to)
           ->orderBy('e.timestamp', 'ASC');

        $query = $qb->getQuery();
        
        // Логируем SQL запрос
        $this->dbLogger->log(
            'sql',
            'info',
            'Find rates in period query',
            [
                'sql' => $query->getSQL(),
                'parameters' => $query->getParameters(),
                'base_currency' => $baseCurrency->getCode(),
                'quote_currency' => $quoteCurrency->getCode(),
                'from_date' => $from->format('Y-m-d H:i:s'),
                'to_date' => $to->format('Y-m-d H:i:s'),
                'entity_class' => $entityClass
            ]
        );

        return $query->getResult();
    }

    public function save(ExchangeRateRecord $exchangeRateRecord): void
    {
        // Логируем операцию сохранения
        $this->dbLogger->log(
            'sql',
            'info',
            'Save exchange rate record',
            [
                'entity_class' => get_class($exchangeRateRecord),
                'timestamp' => $exchangeRateRecord->getTimestamp()->format('Y-m-d H:i:s'),
                'rate' => $exchangeRateRecord->getRate()->getRate()
            ]
        );

        $this->entityManager->persist($exchangeRateRecord);
        $this->entityManager->flush();
    }

    public function deleteOldRecords(\DateTimeImmutable $olderThan): int
    {
        $deletedCount = 0;
        
        // Удаляем старые записи из всех таблиц валютных пар
        foreach (ExchangeRateEntityFactory::getSupportedPairs() as $pairCode) {
            $baseCurrency = new Currency(substr($pairCode, 0, 3));
            $quoteCurrency = new Currency(substr($pairCode, 3, 3));
            $entityClass = ExchangeRateEntityFactory::getEntityClass($baseCurrency, $quoteCurrency);
            
            $qb = $this->entityManager->createQueryBuilder();
            $qb->delete($entityClass, 'e')
               ->where('e.timestamp < :olderThan')
               ->setParameter('olderThan', $olderThan);
            
            $query = $qb->getQuery();
            
            // Логируем SQL запрос удаления
            $this->dbLogger->log(
                'sql',
                'info',
                'Delete old records query',
                [
                    'sql' => $query->getSQL(),
                    'parameters' => $query->getParameters(),
                    'pair_code' => $pairCode,
                    'entity_class' => $entityClass,
                    'older_than' => $olderThan->format('Y-m-d H:i:s')
                ]
            );
            
            $deletedCount += $query->execute();
        }
        
        // Логируем общий результат удаления
        $this->dbLogger->log(
            'sql',
            'info',
            'Delete old records completed',
            [
                'total_deleted' => $deletedCount,
                'older_than' => $olderThan->format('Y-m-d H:i:s')
            ]
        );
        
        return $deletedCount;
    }

    public function getStatistics(Currency $baseCurrency, Currency $quoteCurrency): array
    {
        $entityClass = ExchangeRateEntityFactory::getEntityClass($baseCurrency, $quoteCurrency);
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('MIN(e.rate) as min_rate, MAX(e.rate) as max_rate, AVG(e.rate) as avg_rate, COUNT(e.id) as total_records')
           ->from($entityClass, 'e');

        $query = $qb->getQuery();
        
        // Логируем SQL запрос статистики
        $this->dbLogger->log(
            'sql',
            'info',
            'Get statistics query',
            [
                'sql' => $query->getSQL(),
                'parameters' => $query->getParameters(),
                'base_currency' => $baseCurrency->getCode(),
                'quote_currency' => $quoteCurrency->getCode(),
                'entity_class' => $entityClass
            ]
        );

        $result = $query->getSingleResult();

        $statistics = [
            'min_rate' => (float) $result['min_rate'],
            'max_rate' => (float) $result['max_rate'],
            'avg_rate' => (float) $result['avg_rate'],
            'total_records' => (int) $result['total_records']
        ];
        
        // Логируем результат статистики
        $this->dbLogger->log(
            'sql',
            'info',
            'Statistics result',
            [
                'statistics' => $statistics,
                'base_currency' => $baseCurrency->getCode(),
                'quote_currency' => $quoteCurrency->getCode()
            ]
        );

        return $statistics;
    }
} 