<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\LogEntry;

/**
 * Интерфейс репозитория для работы с логами
 */
interface LogEntryRepositoryInterface
{
    /**
     * Сохранить запись лога
     */
    public function save(LogEntry $logEntry): void;

    /**
     * Найти логи по каналу
     */
    public function findByChannel(string $channel): array;

    /**
     * Найти логи по уровню
     */
    public function findByLevel(string $level): array;

    /**
     * Найти логи за период
     */
    public function findByPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): array;

    /**
     * Найти логи по каналу и уровню
     */
    public function findByChannelAndLevel(string $channel, string $level): array;

    /**
     * Получить последние логи
     */
    public function findLatest(int $limit = 100): array;

    /**
     * Удалить старые логи
     */
    public function deleteOldRecords(\DateTimeImmutable $olderThan): int;
} 