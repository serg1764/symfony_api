<?php

declare(strict_types=1);

namespace App\Application\Handler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;

#[AsMessageHandler]
class FailedMessageHandler
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(Envelope $envelope): void
    {
        $message = $envelope->getMessage();
        
        $this->logger->error('Message processing failed', [
            'message_class' => get_class($message),
            'message_data' => $this->extractMessageData($message),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    private function extractMessageData(object $message): array
    {
        if (method_exists($message, 'getFromCurrency') && method_exists($message, 'getToCurrency')) {
            return [
                'from_currency' => $message->getFromCurrency()->getCode(),
                'to_currency' => $message->getToCurrency()->getCode()
            ];
        }
        
        return ['message' => 'Unknown message structure'];
    }
}
