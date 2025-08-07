<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Message\FetchRateMessage;
use App\Application\Message\SaveRateMessage;
use App\Domain\ValueObject\ExchangeRate;
use App\Infrastructure\External\ExchangeRateApiInterface;
use App\Service\DbLoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class FetchRateMessageHandler
{
    public function __construct(
        private readonly ExchangeRateApiInterface $exchangeRateApi,
        private readonly MessageBusInterface $messageBus,
        private readonly DbLoggerInterface $logger
    ) {
    }

    public function __invoke(FetchRateMessage $message): void
    {
        $fromCurrency = $message->getFromCurrency();
        $toCurrency = $message->getToCurrency();

        $this->logger->log('exchange', 'info', 'Fetching exchange rate', [
            'from' => $fromCurrency->getCode(),
            'to' => $toCurrency->getCode()
        ]);

        try {
            $exchangeRate = $this->exchangeRateApi->getExchangeRate($fromCurrency, $toCurrency);
            
            $saveMessage = new SaveRateMessage($fromCurrency, $toCurrency, $exchangeRate);
            $this->messageBus->dispatch($saveMessage);

            $this->logger->log('exchange', 'saveMessage', 'Данные $saveMessage', [
                'saveMessage' => $saveMessage
            ]);

            $this->logger->log('exchange', 'info', 'Exchange rate fetched and queued for saving', [
                'from' => $fromCurrency->getCode(),
                'to' => $toCurrency->getCode(),
                'rate' => $exchangeRate->getRate()
            ]);
        } catch (\Exception $e) {
            $this->logger->log('exchange', 'error', 'Failed to fetch exchange rate', [
                'from' => $fromCurrency->getCode(),
                'to' => $toCurrency->getCode(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 