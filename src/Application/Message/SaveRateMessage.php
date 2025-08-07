<?php

declare(strict_types=1);

namespace App\Application\Message;

use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;

class SaveRateMessage implements \JsonSerializable
{
    public function __construct(
        private readonly Currency $fromCurrency,
        private readonly Currency $toCurrency,
        private readonly ExchangeRate $exchangeRate
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

    public function getExchangeRate(): ExchangeRate
    {
        return $this->exchangeRate;
    }

    public function toArray(): array
    {
        return [
            'fromCurrency' => $this->fromCurrency->getCode(),
            'toCurrency' => $this->toCurrency->getCode(),
            'exchangeRate' => [
                'rate' => $this->exchangeRate->getRate(),
                'timestamp' => $this->exchangeRate->getTimestamp()->format('Y-m-d H:i:s')
            ]
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
} 