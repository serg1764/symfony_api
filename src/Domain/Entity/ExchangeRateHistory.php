<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность для хранения истории обменных курсов
 */
#[ORM\Entity]
#[ORM\Table(name: 'exchange_rate_history')]
#[ORM\Index(columns: ['base_currency', 'quote_currency'], name: 'idx_exchange_rate_currencies')]
#[ORM\Index(columns: ['timestamp'], name: 'idx_exchange_rate_timestamp')]
class ExchangeRateHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 3)]
    private string $baseCurrency;

    #[ORM\Column(type: 'string', length: 3)]
    private string $quoteCurrency;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 8)]
    private string $rate;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $timestamp;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Currency $baseCurrency,
        Currency $quoteCurrency,
        ExchangeRate $exchangeRate
    ) {
        $this->baseCurrency = $baseCurrency->getCode();
        $this->quoteCurrency = $quoteCurrency->getCode();
        $this->rate = (string) $exchangeRate->getRate();
        $this->timestamp = $exchangeRate->getTimestamp();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getBaseCurrency(): Currency
    {
        return new Currency($this->baseCurrency);
    }

    public function getQuoteCurrency(): Currency
    {
        return new Currency($this->quoteCurrency);
    }

    public function getRate(): ExchangeRate
    {
        return new ExchangeRate((float) $this->rate, $this->timestamp);
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPairCode(): string
    {
        return $this->baseCurrency . $this->quoteCurrency;
    }
} 