<?php

declare(strict_types=1);

namespace App\Application\Message;

use App\Domain\ValueObject\Currency;

class FetchRateMessage
{
    public function __construct(
        private readonly Currency $fromCurrency,
        private readonly Currency $toCurrency
    ) {
    }

    public function getFromCurrency(): Currency
    {
        return $this->fromCurrency;
    }

    public function getToCurrency(): Currency
    {
        return $this->toCurrency;
    }
} 