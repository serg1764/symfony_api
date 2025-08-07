<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use App\Service\DbLoggerInterface;

class DbLoggerTest extends TestCase
{
    public function testLogMethod(): void
    {
        // Создаем мок для Connection
        $connection = $this->createMock(Connection::class);
        
        // Ожидаем, что executeStatement будет вызван с правильными параметрами
        $connection->expects($this->once())
            ->method('executeStatement')
            ->with(
                'INSERT INTO log (channel, level, message, context, created_at) VALUES (?, ?, ?, ?, ?)',
                [
                    'test',
                    'info',
                    'Test message',
                    '{"key":"value"}',
                    $this->isInstanceOf(\DateTime::class)
                ]
            );
        
        $dbLogger = new \App\Service\DbLogger($connection);
        
        // Вызываем метод log
        $dbLogger->log('test', 'info', 'Test message', ['key' => 'value']);
    }
} 