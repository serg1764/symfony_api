<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Message\SaveRateMessage;
use App\Domain\Factory\ExchangeRateEntityFactory;
use App\Domain\Repository\ExchangeRateHistoryRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SaveRateMessageHandler
{
    public function __construct(
        private readonly ExchangeRateHistoryRepositoryInterface $exchangeRateHistoryRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(SaveRateMessage $message): void
    {
        $fromCurrency = $message->getFromCurrency();
        $toCurrency = $message->getToCurrency();
        $exchangeRate = $message->getExchangeRate();

        $this->logger->info('Saving exchange rate', [
            'from' => $fromCurrency->getCode(),
            'to' => $toCurrency->getCode(),
            'rate' => $exchangeRate->getRate(),
            'timestamp' => $exchangeRate->getTimestamp()->format('Y-m-d H:i:s')
        ]);

        try {
            $history = ExchangeRateEntityFactory::createEntity(
                $fromCurrency,
                $toCurrency,
                $exchangeRate
            );

            $this->exchangeRateHistoryRepository->save($history);

            $this->logger->info('Exchange rate saved successfully', [
                'from' => $fromCurrency->getCode(),
                'to' => $toCurrency->getCode(),
                'rate' => $exchangeRate->getRate()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save exchange rate', [
                'from' => $fromCurrency->getCode(),
                'to' => $toCurrency->getCode(),
                'rate' => $exchangeRate->getRate(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 