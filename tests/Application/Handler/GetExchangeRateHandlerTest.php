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
use Psr\Log\LoggerInterface;

/**
 * Тесты для Application Handler GetExchangeRateHandler
 */
class GetExchangeRateHandlerTest extends TestCase
{
    private ExchangeRateHistoryRepositoryInterface $repository;
    private ExchangeRateApiInterface $api;
    private LoggerInterface $logger;
    private GetExchangeRateHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ExchangeRateHistoryRepositoryInterface::class);
        $this->api = $this->createMock(ExchangeRateApiInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new GetExchangeRateHandler($this->repository, $this->api, $this->logger);
    }

    /**
     * @test
     */
    public function it_returns_historical_rate_when_date_provided(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(0.85, $date);

        $history = $this->createMock(ExchangeRateHistory::class);
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
     * @test
     */
    public function it_returns_latest_rate_when_no_date_provided(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(0.85, $date);

        $history = $this->createMock(ExchangeRateHistory::class);
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
     * @test
     */
    public function it_returns_external_api_rate_when_no_history(): void
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
     * @test
     */
    public function it_logs_request(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(0.85, $date);

        $history = $this->createMock(ExchangeRateHistory::class);
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