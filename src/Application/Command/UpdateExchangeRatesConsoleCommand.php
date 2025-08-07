<?php

namespace App\Application\Command;

use App\Infrastructure\Service\ExchangeRateUpdateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Консольная команда для обновления обменных курсов
 */
#[AsCommand(
    name: 'app:exchange-rates:update',
    description: 'Обновить обменные курсы для всех активных пар валют'
)]
class UpdateExchangeRatesConsoleCommand extends Command
{
    public function __construct(
        private ExchangeRateUpdateService $updateService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('cleanup', 'c', InputOption::VALUE_NONE, 'Очистить старые записи после обновления')
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'Количество дней для хранения записей', 30)
            ->setHelp('Эта команда обновляет обменные курсы для всех активных пар валют');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Обновление обменных курсов');

        try {
            // Обновляем курсы
            $updatedCount = $this->updateService->updateAllRates();

            $io->success(sprintf('Обновлено курсов: %d', $updatedCount));

            // Очищаем старые записи если указана опция
            if ($input->getOption('cleanup')) {
                $daysToKeep = (int) $input->getOption('days');
                $deletedCount = $this->updateService->cleanupOldRecords($daysToKeep);

                $io->info(sprintf('Удалено старых записей: %d (храним %d дней)', $deletedCount, $daysToKeep));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Произошла ошибка при обновлении курсов: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 