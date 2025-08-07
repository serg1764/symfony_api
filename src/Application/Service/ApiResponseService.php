<?php

namespace App\Application\Service;

use App\Application\DTO\GetExchangeRateQuery;
use App\Application\Handler\GetExchangeRateHandler;
use App\Domain\ValueObject\Currency;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class ApiResponseService implements ApiResponseServiceInterface
{
    public function __construct(
        private GetExchangeRateHandler $handler,
        private CurrencyValidationServiceInterface $validationService,
        private ExchangeRateStatisticsServiceInterface $statisticsService,
        private DateService $dateService,
        private LoggerInterface $logger
    ) {}

    /**
     * Создает успешный ответ
     */
    public function createSuccessResponse(array $data, string $message = ''): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'error' => ''
        ]);
    }

    /**
     * Создает ответ с ошибкой
     */
    public function createErrorResponse(string $error, int $statusCode = Response::HTTP_BAD_REQUEST, ?array $data = null): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => $error,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Создает ответ для списка валют
     */
    public function createCurrenciesResponse(array $currencies): JsonResponse
    {
        return $this->createSuccessResponse([
            'currencies' => $currencies,
            'total' => count($currencies)
        ], 'Список поддерживаемых валют');
    }

    /**
     * Создает ответ для статистики
     */
    public function createStatisticsResponse(string $baseCurrency, string $quoteCurrency, array $statistics): JsonResponse
    {
        return $this->createSuccessResponse([
            'base_currency' => $baseCurrency,
            'quote_currency' => $quoteCurrency,
            'statistics' => $statistics
        ], 'Статистика по курсам находится в разработке!!!');
    }

    /**
     * Получить обменный курс (полная логика)
     */
    public function getExchangeRate(string $baseCurrency, string $quoteCurrency, ?\DateTimeImmutable $date = null): JsonResponse
    {
        try {
            // Валидация валют
            $this->validationService->validateCurrencyPair($baseCurrency, $quoteCurrency);

            // Валидация даты
            if ($date !== null) {
                $this->dateService->validateDate($date);
            }

            $query = new GetExchangeRateQuery($baseCurrency, $quoteCurrency, $date);
            $response = $this->handler->handle($query);

            return $this->createSuccessResponse(
                $response->toArray(),
                'Обменный курс получен успешно'
            );

        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Неверные параметры запроса', [
                'base_currency' => $baseCurrency,
                'quote_currency' => $quoteCurrency,
                'date' => $date?->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            $this->logger->error('Ошибка получения обменного курса', [
                'base_currency' => $baseCurrency,
                'quote_currency' => $quoteCurrency,
                'date' => $date?->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResponse('Внутренняя ошибка сервера', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Получить список поддерживаемых валют (полная логика)
     */
    public function getSupportedCurrencies(): JsonResponse
    {
        return $this->createCurrenciesResponse(Currency::getSupportedCurrencies());
    }

    /**
     * Получить статистику по курсам (полная логика)
     */
    public function getStatistics(string $baseCurrency, string $quoteCurrency): JsonResponse
    {
        try {
            $this->validationService->validateCurrencyPair($baseCurrency, $quoteCurrency);

            $statistics = $this->statisticsService->getStatistics($baseCurrency, $quoteCurrency);

            return $this->createStatisticsResponse($baseCurrency, $quoteCurrency, $statistics);

        } catch (\InvalidArgumentException $e) {
            return $this->createErrorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


} 