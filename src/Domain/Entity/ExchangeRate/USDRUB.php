<?php

declare(strict_types=1);

namespace App\Domain\Entity\ExchangeRate;

use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\ValueObject\ExchangeRate;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность для хранения истории курса USD/RUB
 */
#[ORM\Entity]
#[ORM\Table(name: 'exchange_rate_usd_rub')]
#[ORM\Index(columns: ['timestamp'], name: 'idx_exchange_rate_usd_rub_timestamp')]
class USDRUB extends ExchangeRateRecord
{
    public function __construct(ExchangeRate $exchangeRate)
    {
        parent::__construct($exchangeRate);
    }

    public static function getTableName(): string
    {
        return 'exchange_rate_usd_rub';
    }
}