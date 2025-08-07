<?php

namespace App\Domain\Event;

use App\Domain\Entity\ExchangeRateHistory;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Событие обновления обменного курса
 */
class ExchangeRateUpdatedEvent extends Event
{
    private ExchangeRateHistory $exchangeRateHistory;

    public function __construct(ExchangeRateHistory $exchangeRateHistory)
    {
        $this->exchangeRateHistory = $exchangeRateHistory;
    }

    public function getExchangeRateHistory(): ExchangeRateHistory
    {
        return $this->exchangeRateHistory;
    }
} 