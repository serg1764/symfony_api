<?php

namespace App\Tests\Presentation\Controller;

use App\Application\DTO\ExchangeRateResponse;
use App\Application\Handler\GetExchangeRateHandler;
use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Тесты для API контроллера ExchangeRateController
 */
class ExchangeRateControllerTest extends TestCase
{
    private GetExchangeRateHandler $handler;
    private LoggerInterface $logger;
    private \App\Presentation\Controller\ExchangeRateController $controller;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(GetExchangeRateHandler::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->controller = new \App\Presentation\Controller\ExchangeRateController($this->handler, $this->logger);
    }

    /**
     * @test
     */
    public function it_returns_exchange_rate_for_valid_currencies(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $exchangeRate = new ExchangeRate(0.85);
        
        $response = new ExchangeRateResponse('USD', 'EUR', 0.85, $exchangeRate->getTimestamp(), 'historical');

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $request = new Request();
        $result = $this->controller->getExchangeRate('USD', 'EUR', $request);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('USD', $data['data']['base_currency']);
        $this->assertEquals('EUR', $data['data']['quote_currency']);
        $this->assertEquals(0.85, $data['data']['rate']);
    }

    /**
     * @test
     */
    public function it_returns_exchange_rate_with_date_parameter(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(0.85, $date);
        
        $response = new ExchangeRateResponse('USD', 'EUR', 0.85, $date, 'historical');

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $request = new Request(['date' => '2024-08-04T10:00:00']);
        $result = $this->controller->getExchangeRate('USD', 'EUR', $request);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_error_for_invalid_base_currency(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('warning');

        $result = $this->controller->getExchangeRate('INVALID', 'EUR', new Request());

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(400, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertNotNull($data['error']);
    }

    /**
     * @test
     */
    public function it_returns_error_for_invalid_quote_currency(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('warning');

        $result = $this->controller->getExchangeRate('USD', 'INVALID', new Request());

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(400, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertNotNull($data['error']);
    }

    /**
     * @test
     */
    public function it_returns_supported_currencies(): void
    {
        $result = $this->controller->getSupportedCurrencies();

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('currencies', $data['data']);
        $this->assertArrayHasKey('total', $data['data']);
        $this->assertContains('USD', $data['data']['currencies']);
        $this->assertContains('EUR', $data['data']['currencies']);
    }

    /**
     * @test
     */
    public function it_returns_statistics_for_valid_currencies(): void
    {
        $result = $this->controller->getStatistics('USD', 'EUR');

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('USD', $data['data']['base_currency']);
        $this->assertEquals('EUR', $data['data']['quote_currency']);
        $this->assertArrayHasKey('statistics', $data['data']);
    }

    /**
     * @test
     */
    public function it_returns_error_for_invalid_currencies_in_statistics(): void
    {
        $result = $this->controller->getStatistics('INVALID', 'EUR');

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(400, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertNotNull($data['error']);
    }
} 