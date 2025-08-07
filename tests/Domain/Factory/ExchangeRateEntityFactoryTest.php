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
     * @test
     */
    public function it_creates_usd_eur_entity(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        $exchangeRate = new ExchangeRate(1.25);

        $entity = ExchangeRateEntityFactory::createEntity($baseCurrency, $quoteCurrency, $exchangeRate);

        $this->assertInstanceOf(USDEUR::class, $entity);
        $this->assertEquals(1.25, $entity->getRate()->getRate());
    }

    /**
     * @test
     */
    public function it_creates_eur_usd_entity(): void
    {
        $baseCurrency = new Currency('EUR');
        $quoteCurrency = new Currency('USD');
        $exchangeRate = new ExchangeRate(0.80);

        $entity = ExchangeRateEntityFactory::createEntity($baseCurrency, $quoteCurrency, $exchangeRate);

        $this->assertInstanceOf(EURUSD::class, $entity);
        $this->assertEquals(0.80, $entity->getRate()->getRate());
    }

    /**
     * @test
     */
    public function it_throws_exception_for_unsupported_pair(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity for currency pair USDGBP not found');

        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('GBP');
        $exchangeRate = new ExchangeRate(1.25);

        ExchangeRateEntityFactory::createEntity($baseCurrency, $quoteCurrency, $exchangeRate);
    }

    /**
     * @test
     */
    public function it_returns_correct_entity_class(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');

        $entityClass = ExchangeRateEntityFactory::getEntityClass($baseCurrency, $quoteCurrency);

        $this->assertEquals(USDEUR::class, $entityClass);
    }

    /**
     * @test
     */
    public function it_returns_correct_table_name(): void
    {
        $baseCurrency = new Currency('EUR');
        $quoteCurrency = new Currency('USD');

        $tableName = ExchangeRateEntityFactory::getTableName($baseCurrency, $quoteCurrency);

        $this->assertEquals('exchange_rate_eur_usd', $tableName);
    }

    /**
     * @test
     */
    public function it_checks_supported_pairs(): void
    {
        $this->assertTrue(ExchangeRateEntityFactory::isSupported(new Currency('USD'), new Currency('EUR')));
        $this->assertTrue(ExchangeRateEntityFactory::isSupported(new Currency('EUR'), new Currency('USD')));
        $this->assertFalse(ExchangeRateEntityFactory::isSupported(new Currency('USD'), new Currency('GBP')));
    }

    /**
     * @test
     */
    public function it_returns_supported_pairs(): void
    {
        $supportedPairs = ExchangeRateEntityFactory::getSupportedPairs();

        $this->assertContains('USDEUR', $supportedPairs);
        $this->assertContains('EURUSD', $supportedPairs);
        $this->assertCount(2, $supportedPairs);
    }
} 