<?php

declare(strict_types=1);

namespace App\Domain\Factory;

use App\Domain\Entity\ExchangeRate\EURUSD;
use App\Domain\Entity\ExchangeRate\USDEUR;
use App\Domain\Entity\ExchangeRate\USDGBP;
use App\Domain\Entity\ExchangeRate\GBPUSD;
use App\Domain\Entity\ExchangeRate\USDRUB;
use App\Domain\Entity\ExchangeRate\RUBUSD;
use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;

/**
 * Фабрика для создания сущностей обменных курсов
 */
class ExchangeRateEntityFactory
{
    private const ENTITY_MAP = [
        'USDEUR' => USDEUR::class,
        'EURUSD' => EURUSD::class,
        'USDGBP' => USDGBP::class,
        'GBPUSD' => GBPUSD::class,
        'USDRUB' => USDRUB::class,
        'RUBUSD' => RUBUSD::class,
        // Добавляйте новые пары здесь
    ];

    public static function createEntity(
        Currency $baseCurrency,
        Currency $quoteCurrency,
        ExchangeRate $exchangeRate
    ): ExchangeRateRecord {
        $pairCode = $baseCurrency->getCode() . $quoteCurrency->getCode();
        
        if (!isset(self::ENTITY_MAP[$pairCode])) {
            throw new \InvalidArgumentException(
                sprintf('Entity for currency pair %s not found', $pairCode)
            );
        }

        $entityClass = self::ENTITY_MAP[$pairCode];
        return new $entityClass($exchangeRate);
    }

    public static function getEntityClass(Currency $baseCurrency, Currency $quoteCurrency): string
    {
        $pairCode = $baseCurrency->getCode() . $quoteCurrency->getCode();
        
        if (!isset(self::ENTITY_MAP[$pairCode])) {
            throw new \InvalidArgumentException(
                sprintf('Entity class for currency pair %s not found', $pairCode)
            );
        }

        return self::ENTITY_MAP[$pairCode];
    }

    public static function getTableName(Currency $baseCurrency, Currency $quoteCurrency): string
    {
        $entityClass = self::getEntityClass($baseCurrency, $quoteCurrency);
        return $entityClass::getTableName();
    }

    public static function isSupported(Currency $baseCurrency, Currency $quoteCurrency): bool
    {
        $pairCode = $baseCurrency->getCode() . $quoteCurrency->getCode();
        return isset(self::ENTITY_MAP[$pairCode]);
    }

    public static function getSupportedPairs(): array
    {
        return array_keys(self::ENTITY_MAP);
    }
} 