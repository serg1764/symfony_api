<?php

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\ExchangeRate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Value Object ExchangeRate
 */
class ExchangeRateTest extends TestCase
{
    /**
     * Проверяет создание курса обмена с валидным значением
     */
    public function testItCreatesExchangeRateWithValidRate(): void
    {
        $exchangeRate = new ExchangeRate(1.25);
        
        $this->assertEquals(1.25, $exchangeRate->getRate());
        $this->assertEquals('1.25', (string) $exchangeRate);
    }

    /**
     * Проверяет создание курса обмена с пользовательской временной меткой
     */
    public function testItCreatesExchangeRateWithCustomTimestamp(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(1.25, $timestamp);
        
        $this->assertEquals(1.25, $exchangeRate->getRate());
        $this->assertEquals($timestamp, $exchangeRate->getTimestamp());
    }

    /**
     * Проверяет использование текущей временной метки при отсутствии пользовательской
     */
    public function testItUsesCurrentTimestampWhenNotProvided(): void
    {
        $before = new \DateTimeImmutable();
        $exchangeRate = new ExchangeRate(1.25);
        $after = new \DateTimeImmutable();
        
        $this->assertGreaterThanOrEqual($before, $exchangeRate->getTimestamp());
        $this->assertLessThanOrEqual($after, $exchangeRate->getTimestamp());
    }

    /**
     * Проверяет выброс исключения для отрицательного курса
     */
    public function testItThrowsExceptionForNegativeRate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Обменный курс должен быть положительным числом');
        
        new ExchangeRate(-1.25);
    }

    /**
     * Проверяет выброс исключения для нулевого курса
     */
    public function testItThrowsExceptionForZeroRate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Обменный курс должен быть положительным числом');
        
        new ExchangeRate(0);
    }

    /**
     * Проверяет выброс исключения для слишком высокого курса
     */
    public function testItThrowsExceptionForTooHighRate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Обменный курс слишком высокий');
        
        new ExchangeRate(2000000);
    }

    /**
     * Проверяет равенство курсов с одинаковыми значениями
     */
    public function testItEqualsAnotherRateWithSameValues(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $rate1 = new ExchangeRate(1.25, $timestamp);
        $rate2 = new ExchangeRate(1.25, $timestamp);
        
        $this->assertTrue($rate1->equals($rate2));
    }

    /**
     * Проверяет неравенство курсов с разными значениями
     */
    public function testItNotEqualsAnotherRateWithDifferentValues(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $rate1 = new ExchangeRate(1.25, $timestamp);
        $rate2 = new ExchangeRate(1.50, $timestamp);
        
        $this->assertFalse($rate1->equals($rate2));
    }

    /**
     * Проверяет создание из float значения
     */
    public function testItCreatesFromFloat(): void
    {
        $exchangeRate = new ExchangeRate(1.25);
        
        $this->assertEquals(1.25, $exchangeRate->getRate());
        $this->assertIsFloat($exchangeRate->getRate());
    }

    /**
     * Проверяет создание из float значения с временной меткой
     */
    public function testItCreatesFromFloatWithTimestamp(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(1.25, $timestamp);
        
        $this->assertEquals(1.25, $exchangeRate->getRate());
        $this->assertEquals($timestamp, $exchangeRate->getTimestamp());
    }
} 