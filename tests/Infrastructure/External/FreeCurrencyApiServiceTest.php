<?php

namespace App\Tests\Infrastructure\External;

use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use App\Infrastructure\External\FreeCurrencyApiService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use RuntimeException;

/**
 * Тесты для внешнего API сервиса FreeCurrencyApiService
 */
class FreeCurrencyApiServiceTest extends TestCase
{
    private HttpClientInterface|MockObject $httpClient;
    private FreeCurrencyApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        // Создаем mock для DbLogger
        $dbLogger = $this->getMockBuilder(\App\Service\DbLoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->service = new FreeCurrencyApiService($this->httpClient, 'test-api-key', $dbLogger);
    }

    public function testItReturnsExchangeRateFromApi(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([
            'data' => [
                'EUR' => 0.85
            ]
        ]);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', $this->stringContains('freecurrencyapi.com'))
            ->willReturn($response);

        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        
        $exchangeRate = $this->service->getExchangeRate($baseCurrency, $quoteCurrency);

        $this->assertInstanceOf(ExchangeRate::class, $exchangeRate);
        $this->assertEquals(0.85, $exchangeRate->getRate());
    }

    public function testItThrowsExceptionWhenApiKeyMissing(): void
    {
        $dbLogger = $this->getMockBuilder(\App\Service\DbLoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $service = new FreeCurrencyApiService($this->httpClient, '', $dbLogger);
        
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('API ключ не настроен');
        
        $service->getExchangeRate($baseCurrency, $quoteCurrency);
    }

    public function testItThrowsExceptionWhenApiFails(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('API Error'));

        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Не удалось получить курс валют USD/EUR');
        
        $this->service->getExchangeRate($baseCurrency, $quoteCurrency);
    }

    public function testItThrowsExceptionForInvalidApiResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([
            'data' => [
                'USD' => 1.0
            ]
        ]);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Неверный ответ от API');
        
        $this->service->getExchangeRate($baseCurrency, $quoteCurrency);
    }

    public function testItReturnsFalseWhenApiKeyMissingForAvailabilityCheck(): void
    {
        $dbLogger = $this->getMockBuilder(\App\Service\DbLoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $service = new FreeCurrencyApiService($this->httpClient, '', $dbLogger);
        
        $this->assertFalse($service->isAvailable());
    }

    public function testItReturnsTrueWhenApiIsAvailable(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->assertTrue($this->service->isAvailable());
    }

    public function testItReturnsFalseWhenApiIsUnavailable(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('API Error'));

        $this->assertFalse($this->service->isAvailable());
    }
} 