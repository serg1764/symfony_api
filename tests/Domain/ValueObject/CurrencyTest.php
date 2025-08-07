<?php

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\Currency;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Value Object Currency
 */
class CurrencyTest extends TestCase
{
    /**
     * Тест: Создание валюты с валидным кодом
     * Проверяет, что Currency создается с правильным кодом и корректно приводится к строке
     */
    public function testItCreatesCurrencyWithValidCode(): void
    {
        $currency = new Currency('USD');
        
        $this->assertEquals('USD', $currency->getCode());
        $this->assertEquals('USD', (string) $currency);
    }

    /**
     * Тест: Конвертация кода валюты в верхний регистр
     * Проверяет, что код валюты автоматически приводится к верхнему регистру
     */
    public function testItConvertsLowercaseToUppercase(): void
    {
        $currency = new Currency('usd');
        
        $this->assertEquals('USD', $currency->getCode());
    }

    /**
     * Тест: Исключение при пустом коде валюты
     * Проверяет, что при попытке создать валюту с пустым кодом выбрасывается исключение
     */
    public function testItThrowsExceptionForEmptyCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Код валюты не может быть пустым');
        
        new Currency('');
    }

    /**
     * Тест: Исключение при неподдерживаемой валюте
     * Проверяет, что при попытке создать валюту с неподдерживаемым кодом выбрасывается исключение
     */
    public function testItThrowsExceptionForUnsupportedCurrency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Неподдерживаемая валюта: XYZ');
        
        new Currency('XYZ');
    }

    /**
     * Тест: Сравнение валют с одинаковым кодом
     * Проверяет, что валюты с одинаковым кодом считаются равными
     */
    public function testItEqualsAnotherCurrencyWithSameCode(): void
    {
        $currency1 = new Currency('USD');
        $currency2 = new Currency('USD');
        
        $this->assertTrue($currency1->equals($currency2));
    }

    /**
     * Тест: Сравнение валют с разными кодами
     * Проверяет, что валюты с разными кодами считаются неравными
     */
    public function testItNotEqualsAnotherCurrencyWithDifferentCode(): void
    {
        $currency1 = new Currency('USD');
        $currency2 = new Currency('EUR');
        
        $this->assertFalse($currency1->equals($currency2));
    }

    /**
     * Тест: Получение списка поддерживаемых валют
     * Проверяет, что метод возвращает массив с поддерживаемыми валютами
     */
    public function testItReturnsSupportedCurrencies(): void
    {
        $supportedCurrencies = Currency::getSupportedCurrencies();
        
        $this->assertIsArray($supportedCurrencies);
        $this->assertContains('USD', $supportedCurrencies);
        $this->assertContains('EUR', $supportedCurrencies);
        $this->assertContains('RUB', $supportedCurrencies);
    }

    /**
     * Тест: Создание валюты из строки через статический метод
     * Проверяет, что статический метод fromString корректно создает объект валюты
     */
    public function testItCreatesFromString(): void
    {
        $currency = Currency::fromString('EUR');
        
        $this->assertEquals('EUR', $currency->getCode());
    }

    /**
     * Тесты: Проверка создания валют для каждой поддерживаемой валюты
     * Проверяют, что каждая поддерживаемая валюта может быть создана корректно
     */
    public function testItAcceptsUSD(): void
    {
        $currency = new Currency('USD');
        $this->assertEquals('USD', $currency->getCode());
    }

    public function testItAcceptsEUR(): void
    {
        $currency = new Currency('EUR');
        $this->assertEquals('EUR', $currency->getCode());
    }

    public function testItAcceptsGBP(): void
    {
        $currency = new Currency('GBP');
        $this->assertEquals('GBP', $currency->getCode());
    }

    public function testItAcceptsJPY(): void
    {
        $currency = new Currency('JPY');
        $this->assertEquals('JPY', $currency->getCode());
    }

    public function testItAcceptsCAD(): void
    {
        $currency = new Currency('CAD');
        $this->assertEquals('CAD', $currency->getCode());
    }

    public function testItAcceptsAUD(): void
    {
        $currency = new Currency('AUD');
        $this->assertEquals('AUD', $currency->getCode());
    }

    public function testItAcceptsCHF(): void
    {
        $currency = new Currency('CHF');
        $this->assertEquals('CHF', $currency->getCode());
    }

    public function testItAcceptsCNY(): void
    {
        $currency = new Currency('CNY');
        $this->assertEquals('CNY', $currency->getCode());
    }

    public function testItAcceptsRUB(): void
    {
        $currency = new Currency('RUB');
        $this->assertEquals('RUB', $currency->getCode());
    }

    public function testItAcceptsINR(): void
    {
        $currency = new Currency('INR');
        $this->assertEquals('INR', $currency->getCode());
    }
} 