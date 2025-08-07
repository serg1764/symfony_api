<?php

namespace App\Application\Command;

use App\Service\DbLoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-db-logger',
    description: 'Тестирование DbLogger сервиса'
)]
class TestDbLoggerConsoleCommand extends Command
{
    public function __construct(
        private readonly DbLoggerInterface $dbLogger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Тестирование DbLogger');

        // Логируем разные типы сообщений
        $this->dbLogger->log('console', 'info', 'Начало выполнения команды', [
            'command' => 'app:test-db-logger',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $this->dbLogger->log('console', 'debug', 'Отладочная информация', [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ]);

        $this->dbLogger->log('console', 'warning', 'Предупреждение о тестировании', [
            'test_mode' => true,
            'environment' => 'development'
        ]);

        $this->dbLogger->log('console', 'error', 'Имитация ошибки', [
            'error_code' => 'TEST_ERROR',
            'stack_trace' => 'Test stack trace'
        ]);

        $io->success('Тестовые логи записаны в таблицу log');

        return Command::SUCCESS;
    }
} 