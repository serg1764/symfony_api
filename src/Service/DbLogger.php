<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Entity\LogEntry;

class DbLogger implements DbLoggerInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function log(string $channel, string $level, string $message, array $context = []): void
    {
        $sql = 'INSERT INTO log (channel, level, message, context, created_at) VALUES (?, ?, ?, ?, ?)';
        
        try {
            // Создаем отдельное соединение для логирования
            $params = $this->connection->getParams();
            $driverManager = \Doctrine\DBAL\DriverManager::getConnection($params);
            
            $driverManager->transactional(function ($conn) use ($sql, $channel, $level, $message, $context) {
                $conn->executeStatement($sql, [
                    $channel,
                    $level,
                    $message,
                    json_encode($context, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    (new \DateTime())->format('Y-m-d H:i:s'),
                ]);
            });
            
            $driverManager->close();
        } catch (\Throwable $e) {
            // fallback лог в stderr и во временный файл
            error_log('DbLogger error: ' . $e->getMessage());
            file_put_contents('/tmp/logger_error.txt', date('c') . ' ' . $e . PHP_EOL, FILE_APPEND);
        }
    }

    public function logWithOrm(string $channel, string $level, string $message, array $context = []): void
    {
        try {
            $logEntry = new LogEntry($channel, $level, $message, $context);
            $this->em->persist($logEntry);
            $this->em->flush();
        } catch (\Throwable $e) {
            // fallback лог
            error_log('DbLogger ORM error: ' . $e->getMessage());
            file_put_contents('/tmp/logger_orm_error.txt', date('c') . ' ' . $e . PHP_EOL, FILE_APPEND);
        }
    }
}
