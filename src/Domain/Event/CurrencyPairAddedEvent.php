<?php

namespace App\Domain\Event;

use App\Domain\Entity\CurrencyPair;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Событие добавления новой пары валют
 */
class CurrencyPairAddedEvent extends Event
{
    private CurrencyPair $currencyPair;

    public function __construct(CurrencyPair $currencyPair)
    {
        $this->currencyPair = $currencyPair;
    }

    public function getCurrencyPair(): CurrencyPair
    {
        return $this->currencyPair;
    }
} 