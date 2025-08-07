<?php

namespace App\Presentation\Controller;

use App\Application\Service\ApiResponseServiceInterface;
use App\Application\Service\DateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для API обменных курсов
 */
#[Route('/exchange-rates')]
class ExchangeRateController extends AbstractController
{
    public function __construct(
        private ApiResponseServiceInterface $apiService,
        private DateService $dateService
    ) {}

    /**
     * Получить обменный курс для пары валют
     */
    #[Route('/{baseCurrency}/{quoteCurrency}', name: 'api_exchange_rate_get', methods: ['GET'])]
    public function getExchangeRate(
        string $baseCurrency,
        string $quoteCurrency,
        Request $request
    ): JsonResponse {
        $date = null;
        $dateParam = $request->query->get('date');
        
        if ($dateParam) {
            try {
                // Поддерживаем различные форматы даты
                $date = $this->dateService->parseDate($dateParam);
            } catch (\Exception $e) {
                return $this->apiService->createErrorResponse(
                    'Неверный формат даты. Используйте формат: YYYY-MM-DD или YYYY-MM-DDTHH:MM:SS',
                    400
                );
            }
        }
        
        return $this->apiService->getExchangeRate($baseCurrency, $quoteCurrency, $date);
    }

    /**
     * Получить список поддерживаемых валют
     */
    #[Route('/currencies', name: 'api_currencies_list', methods: ['GET'])]
    public function getSupportedCurrencies(): JsonResponse
    {
        return $this->apiService->getSupportedCurrencies();
    }

    /**
     * Получить статистику по курсам
     */
    #[Route('/{baseCurrency}/{quoteCurrency}/statistics', name: 'api_exchange_rate_statistics', methods: ['GET'])]
    public function getStatistics(string $baseCurrency, string $quoteCurrency): JsonResponse
    {
        return $this->apiService->getStatistics($baseCurrency, $quoteCurrency);
    }
} 