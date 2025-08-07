<?php

namespace App\Application\EventSubscriber;

use App\Domain\Event\CurrencyPairAddedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Обработчик событий для пар валют
 */
class CurrencyPairEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CurrencyPairAddedEvent::class => 'onCurrencyPairAdded',
        ];
    }

    public function onCurrencyPairAdded(CurrencyPairAddedEvent $event): void
    {
        $currencyPair = $event->getCurrencyPair();
        
        $this->logger->info('Событие: Добавлена новая пара валют', [
            'pair_id' => $currencyPair->getId(),
            'pair_code' => $currencyPair->getPairCode(),
            'base_currency' => $currencyPair->getBaseCurrency()->getCode(),
            'quote_currency' => $currencyPair->getQuoteCurrency()->getCode(),
            'is_active' => $currencyPair->isActive()
        ]);

        // Здесь можно добавить дополнительную логику:
        // - Отправка уведомлений
        // - Обновление кэша
        // - Логирование в внешние системы
        // - Запуск фоновых задач
    }
} 