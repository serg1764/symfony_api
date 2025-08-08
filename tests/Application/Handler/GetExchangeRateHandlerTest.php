<?php

namespace App\Tests\Application\Handler;

use App\Application\DTO\ExchangeRateResponse;
use App\Application\DTO\GetExchangeRateQuery;
use App\Application\Handler\GetExchangeRateHandler;
use App\Domain\Entity\ExchangeRateHistory;
use App\Domain\Repository\ExchangeRateHistoryRepositoryInterface;
use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use App\Infrastructure\External\ExchangeRateApiInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Тесты для обработчика GetExchangeRateHandler
 */
class GetExchangeRateHandlerTest extends TestCase
{
    private ExchangeRateHistoryRepositoryInterface|MockObject $repository;
    private ExchangeRateApiInterface|MockObject $api;
    private LoggerInterface|MockObject $logger;
    private GetExchangeRateHandler $handler;

    /**
     * Настройка тестового окружения перед каждым тестом
     */
    protected function setUp(): void
    {
        $this->repository = $this->createMock(ExchangeRateHistoryRepositoryInterface::class);
        $this->api = $this->createMock(ExchangeRateApiInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new GetExchangeRateHandler($this->repository, $this->api, $this->logger);
    }

    /**
     * Проверяет возврат исторического курса при указании даты
     */
    public function testItReturnsHistoricalRateWhenDateProvided(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(0.85, $date);

        $history = $this->createMock(\App\Domain\Entity\ExchangeRateRecord::class);
        $history->method('getRate')->willReturn($exchangeRate);

        $this->repository
            ->expects($this->once())
            ->method('findRateAtDate')
            ->with($baseCurrency, $quoteCurrency, $date)
            ->willReturn($history);

        $query = new GetExchangeRateQuery('USD', 'EUR', $date);
        $response = $this->handler->handle($query);

        $this->assertInstanceOf(ExchangeRateResponse::class, $response);
        $this->assertEquals('USD', $response->getBaseCurrency());
        $this->assertEquals('EUR', $response->getQuoteCurrency());
        $this->assertEquals(0.85, $response->getRate());
        $this->assertEquals('historical', $response->getSource());
    }

    /**
     * Проверяет возврат последнего курса при отсутствии даты
     */
    public function testItReturnsLatestRateWhenNoDateProvided(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(0.85, $date);

        $history = $this->createMock(\App\Domain\Entity\ExchangeRateRecord::class);
        $history->method('getRate')->willReturn($exchangeRate);

        $this->repository
            ->expects($this->once())
            ->method('findLatestRate')
            ->with($baseCurrency, $quoteCurrency)
            ->willReturn($history);

        $query = new GetExchangeRateQuery('USD', 'EUR');
        $response = $this->handler->handle($query);

        $this->assertInstanceOf(ExchangeRateResponse::class, $response);
        $this->assertEquals('USD', $response->getBaseCurrency());
        $this->assertEquals('EUR', $response->getQuoteCurrency());
        $this->assertEquals(0.85, $response->getRate());
        $this->assertEquals('historical', $response->getSource());
    }

    /**
     * Проверяет возврат курса из внешнего API при отсутствии истории
     */
    public function testItReturnsExternalApiRateWhenNoHistory(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(0.85, $date);

        $this->repository
            ->expects($this->once())
            ->method('findLatestRate')
            ->with($baseCurrency, $quoteCurrency)
            ->willReturn(null);

        $this->api
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with($baseCurrency, $quoteCurrency)
            ->willReturn($exchangeRate);

        $query = new GetExchangeRateQuery('USD', 'EUR');
        $response = $this->handler->handle($query);

        $this->assertInstanceOf(ExchangeRateResponse::class, $response);
        $this->assertEquals('USD', $response->getBaseCurrency());
        $this->assertEquals('EUR', $response->getQuoteCurrency());
        $this->assertEquals(0.85, $response->getRate());
        $this->assertEquals('external_api', $response->getSource());
    }

    /**
     * Проверяет логирование запросов
     */
    public function testItLogsRequest(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(0.85, $date);

        $history = $this->createMock(\App\Domain\Entity\ExchangeRateRecord::class);
        $history->method('getRate')->willReturn($exchangeRate);

        $this->repository
            ->method('findRateAtDate')
            ->willReturn($history);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Запрос обменного курса', $this->arrayHasKey('base_currency'));

        $query = new GetExchangeRateQuery('USD', 'EUR', $date);
        $this->handler->handle($query);
    }
} 