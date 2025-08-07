<?php

namespace App\Application\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

interface ApiResponseServiceInterface
{
    /**
     * Создает успешный ответ
     */
    public function createSuccessResponse(array $data, string $message = ''): JsonResponse;

    /**
     * Создает ответ с ошибкой
     */
    public function createErrorResponse(string $error, int $statusCode = 400, ?array $data = null): JsonResponse;

    /**
     * Создает ответ для списка валют
     */
    public function createCurrenciesResponse(array $currencies): JsonResponse;

    /**
     * Создает ответ для статистики
     */
    public function createStatisticsResponse(string $baseCurrency, string $quoteCurrency, array $statistics): JsonResponse;

    /**
     * Получить обменный курс (полная логика)
     */
    public function getExchangeRate(string $baseCurrency, string $quoteCurrency, ?\DateTimeImmutable $date = null): JsonResponse;

    /**
     * Получить список поддерживаемых валют (полная логика)
     */
    public function getSupportedCurrencies(): JsonResponse;

    /**
     * Получить статистику по курсам (полная логика)
     */
    public function getStatistics(string $baseCurrency, string $quoteCurrency): JsonResponse;
} 