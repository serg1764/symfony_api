<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Тестовый контроллер для проверки работы API
 */
class TestController extends AbstractController
{
    /**
     * Главная страница API
     */
    #[Route('/', name: 'api_home', methods: ['GET'])]
    public function home(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Добро пожаловать в Symfony API!',
            'status' => 'success',
            'error' => '',
            'data' => [
                'version' => '1.0.0',
                'endpoints' => [
                    '/api/test' => 'Тестовый эндпоинт',
                    '/api/users' => 'Список пользователей',
                    '/api/system/info' => 'Информация о системе',
                    '/api/health' => 'Проверка здоровья API'
                ]
            ],
            'timestamp' => new \DateTime()
        ]);
    }

    /**
     * Тестовый эндпоинт для получения данных
     */
    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Тестовый эндпоинт работает!',
            'status' => 'success',
            'error' => '',
            'data' => [
                'id' => 1,
                'name' => 'Тестовый объект',
                'description' => 'Это тестовые данные для проверки API'
            ]
        ]);
    }

    /**
     * Эндпоинт для получения списка пользователей (тестовые данные)
     */
    #[Route('/api/users', name: 'api_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Иван Иванов',
                'email' => 'ivan@example.com',
                'role' => 'admin'
            ],
            [
                'id' => 2,
                'name' => 'Мария Петрова',
                'email' => 'maria@example.com',
                'role' => 'user'
            ],
            [
                'id' => 3,
                'name' => 'Алексей Сидоров',
                'email' => 'alex@example.com',
                'role' => 'moderator'
            ]
        ];

        return new JsonResponse([
            'message' => 'Список пользователей получен',
            'status' => 'success',
            'error' => '',
            'data' => [
                'users' => $users,
                'total' => count($users)
            ]
        ]);
    }

    /**
     * Эндпоинт для создания нового пользователя (тестовый)
     */
    #[Route('/api/users', name: 'api_create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'message' => 'Ошибка при создании пользователя',
                'status' => 'error',
                'error' => 'Неверный формат данных',
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }

        // Имитация создания пользователя
        $newUser = [
            'id' => rand(100, 999),
            'name' => $data['name'] ?? 'Неизвестный пользователь',
            'email' => $data['email'] ?? 'unknown@example.com',
            'role' => $data['role'] ?? 'user',
            'created_at' => new \DateTime()
        ];

        return new JsonResponse([
            'message' => 'Пользователь успешно создан',
            'status' => 'success',
            'error' => '',
            'data' => [
                'user' => $newUser
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Эндпоинт для получения информации о системе
     */
    #[Route('/api/system/info', name: 'api_system_info', methods: ['GET'])]
    public function getSystemInfo(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Информация о системе получена',
            'status' => 'success',
            'error' => '',
            'data' => [
                'system' => [
                    'php_version' => PHP_VERSION,
                    'symfony_version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
                    'server_time' => new \DateTime(),
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true)
                ],
                'environment' => [
                    'app_env' => $this->getParameter('kernel.environment'),
                    'debug' => $this->getParameter('kernel.debug')
                ]
            ]
        ]);
    }

    /**
     * Эндпоинт для проверки здоровья API
     */
    #[Route('/api/health', name: 'api_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'API работает нормально',
            'status' => 'success',
            'error' => '',
            'data' => [
                'health_status' => 'healthy',
                'uptime' => 'API работает нормально',
                'timestamp' => new \DateTime()
            ]
        ]);
    }
} 