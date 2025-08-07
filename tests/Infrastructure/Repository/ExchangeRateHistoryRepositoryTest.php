<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Domain\Entity\ExchangeRate\EURUSD;
use App\Domain\Entity\ExchangeRate\USDEUR;
use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\Factory\ExchangeRateEntityFactory;
use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use App\Infrastructure\Repository\ExchangeRateHistoryRepository;
use App\Service\DbLoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тесты для репозитория истории обменных курсов
 */
class ExchangeRateHistoryRepositoryTest extends TestCase
{
    private ExchangeRateHistoryRepository $repository;
    private EntityManagerInterface|MockObject $entityManager;
    private QueryBuilder|MockObject $queryBuilder;
    private DbLoggerInterface|MockObject $dbLogger;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->dbLogger = $this->createMock(DbLoggerInterface::class);
        $this->repository = new ExchangeRateHistoryRepository($this->entityManager, $this->dbLogger);
    }

    public function testItFindsLatestRate(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $expectedEntity = new USDEUR(new ExchangeRate(1.25));

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('e')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(USDEUR::class, 'e')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('e.timestamp', 'DESC')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->with(1)
            ->willReturnSelf();

        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn($expectedEntity);

        // Ожидаем вызов логирования
        $this->dbLogger->expects($this->once())
            ->method('log');

        $result = $this->repository->findLatestRate($baseCurrency, $quoteCurrency);

        $this->assertSame($expectedEntity, $result);
    }

    public function testItFindsRateAtDate(): void
    {
        $baseCurrency = new Currency('EUR');
        $quoteCurrency = new Currency('USD');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $expectedEntity = new EURUSD(new ExchangeRate(1.15, $date));

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('e')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(EURUSD::class, 'e')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('where')
            ->with('e.timestamp >= :startOfMinute')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with('e.timestamp <= :endOfMinute')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('e.timestamp', 'DESC')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->with(1)
            ->willReturnSelf();

        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn($expectedEntity);

        // Ожидаем вызов логирования
        $this->dbLogger->expects($this->once())
            ->method('log');

        $result = $this->repository->findRateAtDate($baseCurrency, $quoteCurrency, $date);

        $this->assertSame($expectedEntity, $result);
    }

    public function testItSavesExchangeRateRecord(): void
    {
        $record = new USDEUR(new ExchangeRate(1.25));

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($record);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->repository->save($record);
    }

    public function testItThrowsExceptionForUnsupportedCurrencyPair(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity class for currency pair USDJPY not found');

        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('JPY');

        $this->repository->findLatestRate($baseCurrency, $quoteCurrency);
    }
} 