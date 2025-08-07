<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\Message\FetchRateMessage;
use App\Domain\Repository\CurrencyPairRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:schedule-fetch-rates',
    description: 'Schedule fetch rates for all active currency pairs'
)]
class ScheduleFetchRatesConsoleCommand extends Command
{
    public function __construct(
        private readonly CurrencyPairRepositoryInterface $currencyPairRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        file_put_contents('/tmp/cron_marker.log', "[" . date('Y-m-d H:i:s') . "] Команда schedule-fetch-rates запущена\n", FILE_APPEND);

        $output->writeln('🔄 Scheduling fetch rates for all active currency pairs...');

        try {
            $activePairs = $this->currencyPairRepository->findActivePairs();
            
            if (empty($activePairs)) {
                $output->writeln('⚠️  No active currency pairs found.');
                $this->logger->info('No active currency pairs to schedule');
                return Command::SUCCESS;
            }

            $scheduledCount = 0;
            foreach ($activePairs as $pair) {
                $message = new FetchRateMessage(
                    $pair->getBaseCurrency(),
                    $pair->getQuoteCurrency()
                );
                
                $this->messageBus->dispatch($message);
                $scheduledCount++;
                
                $output->writeln(sprintf(
                    '📤 Scheduled fetch for %s/%s',
                    $pair->getBaseCurrency()->getCode(),
                    $pair->getQuoteCurrency()->getCode()
                ));
            }

            $output->writeln(sprintf('✅ Scheduled %d fetch tasks', $scheduledCount));
            $this->logger->info('Scheduled fetch rates', ['count' => $scheduledCount]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('❌ Error scheduling fetch rates: %s', $e->getMessage()));
            $this->logger->error('Failed to schedule fetch rates', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }
} 