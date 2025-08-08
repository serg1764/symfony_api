<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Добро пожаловать в Currency Exchange Rate API!',
            'status' => 'success',
            'error' => '',
            'data' => [
                'version' => '1.0.0',
                'description' => 'API для трекинга обменных курсов валют',
                'web_interface' => 'http://localhost:8080/',
                'endpoints' => [
                    '/api/exchange-rates/{baseCurrency}/{quoteCurrency}' => 'Получить обменный курс',
                    '/api/exchange-rates/currencies' => 'Список поддерживаемых валют',
                    '/api/exchange-rates/{baseCurrency}/{quoteCurrency}/statistics' => 'Статистика по курсам'
                ]
            ],
            'timestamp' => new \DateTime()
        ]);
    }
} 