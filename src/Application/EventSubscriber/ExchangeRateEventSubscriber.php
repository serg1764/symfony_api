<?php

namespace App\Application\EventSubscriber;

use App\Domain\Event\ExchangeRateUpdatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Обработчик событий для обновления курсов валют
 */
class ExchangeRateEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ExchangeRateUpdatedEvent::class => 'onExchangeRateUpdated',
        ];
    }

    public function onExchangeRateUpdated(ExchangeRateUpdatedEvent $event): void
    {
        $exchangeRateHistory = $event->getExchangeRateHistory();
        $rate = $exchangeRateHistory->getRate();
        
        $this->logger->info('Событие: Обновлен курс валют', [
            'pair_code' => $exchangeRateHistory->getBaseCurrency()->getCode() . '/' . $exchangeRateHistory->getQuoteCurrency()->getCode(),
            'rate' => $rate->getRate(),
            'timestamp' => $rate->getTimestamp()->format('Y-m-d H:i:s')
        ]);

        // Здесь можно добавить дополнительную логику:
        // - Отправка уведомлений при значительных изменениях курса
        // - Обновление кэша
        // - Аналитика и метрики
        // - Интеграция с внешними системами
        // - Проверка пороговых значений для алертов
    }
} 