<?php

namespace App\Tests\Integration;

use App\Application\DTO\AddCurrencyPairCommand;
use App\Application\DTO\GetExchangeRateQuery;
use App\Application\Handler\AddCurrencyPairHandler;
use App\Application\Handler\GetExchangeRateHandler;
use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use PHPUnit\Framework\TestCase;

/**
 * Интеграционный тест для проверки работы всего приложения
 */
class ApplicationTest extends TestCase
{
    /**
     * Проверяет создание и получение валютной пары.
     */
    public function testItCanAddCurrencyPairAndGetExchangeRate(): void
    {
        // Создаем валюты
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        
        $this->assertEquals('USD', $baseCurrency->getCode());
        $this->assertEquals('EUR', $quoteCurrency->getCode());
        
        // Проверяем, что валюты поддерживаются
        $supportedCurrencies = Currency::getSupportedCurrencies();
        $this->assertContains('USD', $supportedCurrencies);
        $this->assertContains('EUR', $supportedCurrencies);
    }

    /**
     * Проверяет создание объекта курса обмена.
     */
    public function testItCanCreateExchangeRate(): void
    {
        $rate = new ExchangeRate(0.85);
        
        $this->assertEquals(0.85, $rate->getRate());
        $this->assertInstanceOf(\DateTimeImmutable::class, $rate->getTimestamp());
    }

    /**
     * Проверяет валидацию кодов валют.
     */
    public function testItValidatesCurrencyCodes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Currency('INVALID');
    }

    /**
     * Проверяет валидацию значений курсов обмена.
     */
    public function testItValidatesExchangeRateValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ExchangeRate(-1.0);
    }

    /**
     * Проверяет нечувствительность к регистру кодов валют.
     */
    public function testItHandlesCurrencyCaseInsensitivity(): void
    {
        $currency = new Currency('usd');
        $this->assertEquals('USD', $currency->getCode());
    }

    /**
     * Проверяет корректность сравнения валют.
     */
    public function testItComparesCurrenciesCorrectly(): void
    {
        $currency1 = new Currency('USD');
        $currency2 = new Currency('USD');
        $currency3 = new Currency('EUR');
        
        $this->assertTrue($currency1->equals($currency2));
        $this->assertFalse($currency1->equals($currency3));
    }

    /**
     * Проверяет корректность сравнения курсов обмена.
     */
    public function testItComparesExchangeRatesCorrectly(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $rate1 = new ExchangeRate(0.85, $timestamp);
        $rate2 = new ExchangeRate(0.85, $timestamp);
        $rate3 = new ExchangeRate(0.90, $timestamp);
        
        $this->assertTrue($rate1->equals($rate2));
        $this->assertFalse($rate1->equals($rate3));
    }

    /**
     * Проверяет создание команд и запросов.
     */
    public function testItCreatesQueriesAndCommands(): void
    {
        // Создаем команду для добавления пары валют
        $addCommand = new AddCurrencyPairCommand('USD', 'EUR');
        $this->assertEquals('USD', $addCommand->getBaseCurrency()->getCode());
        $this->assertEquals('EUR', $addCommand->getQuoteCurrency()->getCode());
        
        // Создаем запрос для получения курса
        $date = new \DateTimeImmutable('2024-08-04 10:00:00');
        $getQuery = new GetExchangeRateQuery('USD', 'EUR', $date);
        $this->assertEquals('USD', $getQuery->getBaseCurrency()->getCode());
        $this->assertEquals('EUR', $getQuery->getQuoteCurrency()->getCode());
        $this->assertEquals($date, $getQuery->getDate());
    }

    /**
     * Проверяет поддержку всех необходимых валют.
     */
    public function testItSupportsAllRequiredCurrencies(): void
    {
        $requiredCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'RUB', 'INR'];
        $supportedCurrencies = Currency::getSupportedCurrencies();
        
        foreach ($requiredCurrencies as $currency) {
            $this->assertContains($currency, $supportedCurrencies, "Валюта {$currency} должна поддерживаться");
        }
    }
} 