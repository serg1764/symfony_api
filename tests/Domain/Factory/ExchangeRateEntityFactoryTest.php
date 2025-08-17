<?php

declare(strict_types=1);

namespace App\Tests\Domain\Factory;

use App\Domain\Entity\ExchangeRate\EURUSD;
use App\Domain\Entity\ExchangeRate\USDEUR;
use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\Factory\ExchangeRateEntityFactory;
use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для фабрики сущностей обменных курсов
 */
class ExchangeRateEntityFactoryTest extends TestCase
{
    /**
     * Тест создания сущности USD/EUR
     */
    public function testCreatesUsdEurEntity(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $exchangeRate = new ExchangeRate(1.25);

        $entity = ExchangeRateEntityFactory::createEntity($baseCurrency, $quoteCurrency, $exchangeRate);

        $this->assertInstanceOf(USDEUR::class, $entity);
        $this->assertEquals(1.25, $entity->getRate()->getRate());
    }

    /**
     * Тест создания сущности EUR/USD
     */
    public function testCreatesEurUsdEntity(): void
    {
        $baseCurrency = new Currency('EUR');
        $quoteCurrency = new Currency('USD');
        $exchangeRate = new ExchangeRate(0.80);

        $entity = ExchangeRateEntityFactory::createEntity($baseCurrency, $quoteCurrency, $exchangeRate);

        $this->assertInstanceOf(EURUSD::class, $entity);
        $this->assertEquals(0.80, $entity->getRate()->getRate());
    }

    /**
     * Тест выброса исключения для неподдерживаемой пары валют
     */
    public function testThrowsExceptionForUnsupportedPair(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity for currency pair RUBGBP not found');

        $baseCurrency = new Currency('RUB');
        $quoteCurrency = new Currency('GBP');
        $exchangeRate = new ExchangeRate(1.25);

        ExchangeRateEntityFactory::createEntity($baseCurrency, $quoteCurrency, $exchangeRate);
    }

    /**
     * Тест возврата правильного класса сущности
     */
    public function testReturnsCorrectEntityClass(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');

        $entityClass = ExchangeRateEntityFactory::getEntityClass($baseCurrency, $quoteCurrency);

        $this->assertEquals(USDEUR::class, $entityClass);
    }

    /**
     * Тест возврата правильного имени таблицы
     */
    public function testReturnsCorrectTableName(): void
    {
        $baseCurrency = new Currency('EUR');
        $quoteCurrency = new Currency('USD');

        $tableName = ExchangeRateEntityFactory::getTableName($baseCurrency, $quoteCurrency);

        $this->assertEquals('exchange_rate_eur_usd', $tableName);
    }

    /**
     * Тест проверки поддерживаемых пар валют
     */
    public function testChecksSupportedPairs(): void
    {
        $this->assertTrue(ExchangeRateEntityFactory::isSupported(new Currency('USD'), new Currency('EUR')));
        $this->assertTrue(ExchangeRateEntityFactory::isSupported(new Currency('EUR'), new Currency('USD')));
        $this->assertTrue(ExchangeRateEntityFactory::isSupported(new Currency('USD'), new Currency('GBP')));
        $this->assertFalse(ExchangeRateEntityFactory::isSupported(new Currency('RUB'), new Currency('GBP')));
    }

    /**
     * Тест возврата списка поддерживаемых пар валют
     */
    public function testReturnsSupportedPairs(): void
    {
        $supportedPairs = ExchangeRateEntityFactory::getSupportedPairs();

        $this->assertContains('USDEUR', $supportedPairs);
        $this->assertContains('EURUSD', $supportedPairs);
        $this->assertCount(6, $supportedPairs);
    }
} 