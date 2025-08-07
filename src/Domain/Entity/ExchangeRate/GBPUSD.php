<?php

declare(strict_types=1);

namespace App\Domain\Entity\ExchangeRate;

use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\ValueObject\ExchangeRate;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность для хранения истории курса GBP/USD
 */
#[ORM\Entity]
#[ORM\Table(name: 'exchange_rate_gbp_usd')]
#[ORM\Index(columns: ['timestamp'], name: 'idx_gbp_usd_timestamp')]
class GBPUSD extends ExchangeRateRecord
{
    public function __construct(ExchangeRate $exchangeRate)
    {
        parent::__construct($exchangeRate);
    }

    public static function getTableName(): string
    {
        return 'exchange_rate_gbp_usd';
    }
} 