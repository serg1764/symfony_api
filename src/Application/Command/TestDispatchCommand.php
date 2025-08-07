<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\Message\FetchRateMessage;
use App\Domain\ValueObject\Currency;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'test:dispatch',
    description: 'Test dispatching messages to queues'
)]
class TestDispatchCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('🔄 Testing message dispatch...');

        try {
            $message = new FetchRateMessage(
                new Currency('USD'),
                new Currency('EUR')
            );
            
            $this->commandBus->dispatch($message);
            
            $output->writeln('✅ Message dispatched successfully!');
            $output->writeln('📤 Check RabbitMQ management UI at http://localhost:15672');
            $output->writeln('👤 Login: guest / guest');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('❌ Error dispatching message: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
} 