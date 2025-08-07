<?php

namespace App\Service;

interface DbLoggerInterface
{
    /**
     * Логирует сообщение в базу данных
     *
     * @param string $channel Название подсистемы (например: exchange, worker, api)
     * @param string $level Уровень логирования (например: info, error, debug)
     * @param string $message Сообщение для логирования
     * @param array $context Дополнительные данные (сохраняются как JSON)
     */
    public function log(string $channel, string $level, string $message, array $context = []): void;

    /**
     * Логирует сообщение в базу данных через ORM
     *
     * @param string $channel Название подсистемы (например: exchange, worker, api)
     * @param string $level Уровень логирования (например: info, error, debug)
     * @param string $message Сообщение для логирования
     * @param array $context Дополнительные данные (сохраняются как JSON)
     */
    public function logWithOrm(string $channel, string $level, string $message, array $context = []): void;
} 