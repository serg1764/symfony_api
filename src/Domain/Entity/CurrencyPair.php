<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Currency;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * Сущность пары валют для отслеживания
 */
#[ORM\Entity]
#[ORM\Table(name: 'currency_pairs')]
#[ORM\Index(columns: ['base_currency', 'quote_currency'], name: 'idx_currency_pair')]
class CurrencyPair
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 3)]
    private string $baseCurrency;

    #[ORM\Column(type: 'string', length: 3)]
    private string $quoteCurrency;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(Currency $baseCurrency, Currency $quoteCurrency)
    {
        if ($baseCurrency->equals($quoteCurrency)) {
            throw new InvalidArgumentException('Базовая и котируемая валюта не могут быть одинаковыми');
        }

        $this->baseCurrency = $baseCurrency->getCode();
        $this->quoteCurrency = $quoteCurrency->getCode();
        $this->isActive = true;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBaseCurrency(): Currency
    {
        return new Currency($this->baseCurrency);
    }

    public function getQuoteCurrency(): Currency
    {
        return new Currency($this->quoteCurrency);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPairCode(): string
    {
        return $this->baseCurrency . $this->quoteCurrency;
    }

    public function equals(CurrencyPair $other): bool
    {
        return $this->baseCurrency === $other->baseCurrency && 
               $this->quoteCurrency === $other->quoteCurrency;
    }
} 