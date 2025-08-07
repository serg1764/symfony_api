<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\ExchangeRate;
use Doctrine\ORM\Mapping as ORM;

/**
 * Базовая сущность для хранения записи обменного курса
 */
#[ORM\MappedSuperclass]
abstract class ExchangeRateRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 8)]
    protected string $rate;

    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $timestamp;

    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $createdAt;

    public function __construct(ExchangeRate $exchangeRate)
    {
        $this->rate = (string) $exchangeRate->getRate();
        $this->timestamp = $exchangeRate->getTimestamp();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
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

    abstract public static function getTableName(): string;
} 