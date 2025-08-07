<?php

namespace App\Application\Command;

use App\Application\DTO\AddCurrencyPairCommand;
use App\Application\Handler\AddCurrencyPairHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Консольная команда для добавления пары валют
 */
#[AsCommand(
    name: 'app:currency-pair:add',
    description: 'Добавить новую пару валют для отслеживания'
)]
class AddCurrencyPairConsoleCommand extends Command
{
    public function __construct(
        private AddCurrencyPairHandler $handler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('base-currency', InputArgument::REQUIRED, 'Базовая валюта (например, USD)')
            ->addArgument('quote-currency', InputArgument::REQUIRED, 'Котируемая валюта (например, EUR)')
            ->setHelp('Эта команда добавляет новую пару валют для отслеживания. Пример: app:currency-pair:add USD EUR');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $baseCurrency = $input->getArgument('base-currency');
        $quoteCurrency = $input->getArgument('quote-currency');

        try {
            $command = new AddCurrencyPairCommand($baseCurrency, $quoteCurrency);
            $currencyPair = $this->handler->handle($command);

            $io->success(sprintf(
                'Пара валют %s/%s успешно добавлена (ID: %d)',
                $currencyPair->getBaseCurrency()->getCode(),
                $currencyPair->getQuoteCurrency()->getCode(),
                $currencyPair->getId()
            ));

            return Command::SUCCESS;
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        } catch (\Exception $e) {
            $io->error('Произошла ошибка при добавлении пары валют: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 