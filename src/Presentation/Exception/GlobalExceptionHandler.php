<?php

namespace App\Presentation\Exception;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Глобальный обработчик исключений
 */
class GlobalExceptionHandler
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Логируем исключение
        $this->logger->error('Необработанное исключение', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => $request->getUri(),
            'method' => $request->getMethod()
        ]);

        // Создаем JSON ответ
        $response = new JsonResponse([
            'success' => false,
            'error' => $this->getErrorMessage($exception),
            'data' => null,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);

        // Устанавливаем HTTP статус код
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }

    private function getErrorMessage(\Throwable $exception): string
    {
        // В продакшене не показываем детали исключений
        if ($_ENV['APP_ENV'] === 'prod') {
            return 'Внутренняя ошибка сервера';
        }

        return $exception->getMessage();
    }
} 