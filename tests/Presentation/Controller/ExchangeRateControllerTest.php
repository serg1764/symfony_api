<?php

namespace App\Tests\Presentation\Controller;

use App\Application\Service\ApiResponseServiceInterface;
use App\Application\Service\DateService;
use App\Presentation\Controller\ExchangeRateController;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Тесты для API контроллера ExchangeRateController
 */
class ExchangeRateControllerTest extends TestCase
{
    private ApiResponseServiceInterface|MockObject $apiService;
    private DateService|MockObject $dateService;
    private ExchangeRateController $controller;

    /**
     * Настройка тестового окружения перед каждым тестом
     */
    protected function setUp(): void
    {
        $this->apiService = $this->createMock(ApiResponseServiceInterface::class);
        $this->dateService = $this->createMock(DateService::class);
        $this->controller = new ExchangeRateController($this->apiService, $this->dateService);
    }

    /**
     * Проверяет возврат курса обмена для валидных валют
     */
    public function testItReturnsExchangeRateForValidCurrencies(): void
    {
        $expectedResponse = new JsonResponse(['success' => true, 'data' => ['rate' => 0.85]]);
        
        $this->apiService
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with('USD', 'EUR', null)
            ->willReturn($expectedResponse);

        $request = new Request();
        $result = $this->controller->getExchangeRate('USD', 'EUR', $request);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Проверяет возврат курса обмена с параметром даты
     */
    public function testItReturnsExchangeRateWithDateParameter(): void
    {
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $expectedResponse = new JsonResponse(['success' => true, 'data' => ['rate' => 0.85]]);
        
        $this->dateService
            ->expects($this->once())
            ->method('parseDate')
            ->with('2024-08-04T10:00:00')
            ->willReturn($date);

        $this->apiService
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with('USD', 'EUR', $date)
            ->willReturn($expectedResponse);

        $request = new Request(['date' => '2024-08-04T10:00:00']);
        $result = $this->controller->getExchangeRate('USD', 'EUR', $request);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Проверяет возврат ошибки при неверном формате даты
     */
    public function testItReturnsErrorForInvalidDateFormat(): void
    {
        $expectedResponse = new JsonResponse(['success' => false, 'error' => 'Invalid date'], 400);
        
        $this->dateService
            ->expects($this->once())
            ->method('parseDate')
            ->with('invalid-date')
            ->willThrowException(new \Exception('Invalid date format'));

        $this->apiService
            ->expects($this->once())
            ->method('createErrorResponse')
            ->with('Неверный формат даты. Используйте формат: YYYY-MM-DD или YYYY-MM-DDTHH:MM:SS', 400)
            ->willReturn($expectedResponse);

        $request = new Request(['date' => 'invalid-date']);
        $result = $this->controller->getExchangeRate('USD', 'EUR', $request);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Проверяет возврат списка поддерживаемых валют
     */
    public function testItReturnsSupportedCurrencies(): void
    {
        $expectedResponse = new JsonResponse(['success' => true, 'data' => ['currencies' => ['USD', 'EUR']]]);
        
        $this->apiService
            ->expects($this->once())
            ->method('getSupportedCurrencies')
            ->willReturn($expectedResponse);

        $result = $this->controller->getSupportedCurrencies();

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Проверяет возврат статистики для валидных валют
     */
    public function testItReturnsStatisticsForValidCurrencies(): void
    {
        $expectedResponse = new JsonResponse(['success' => true, 'data' => ['statistics' => []]]);
        
        $this->apiService
            ->expects($this->once())
            ->method('getStatistics')
            ->with('USD', 'EUR')
            ->willReturn($expectedResponse);

        $result = $this->controller->getStatistics('USD', 'EUR');

        $this->assertSame($expectedResponse, $result);
    }
} 