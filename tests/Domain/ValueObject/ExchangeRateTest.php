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
     * @test
     */
    public function it_creates_exchange_rate_with_valid_rate(): void
    {
        $exchangeRate = new ExchangeRate(1.25);
        
        $this->assertEquals(1.25, $exchangeRate->getRate());
        $this->assertEquals('1.25', (string) $exchangeRate);
    }

    /**
     * @test
     */
    public function it_creates_exchange_rate_with_custom_timestamp(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = new ExchangeRate(1.25, $timestamp);
        
        $this->assertEquals(1.25, $exchangeRate->getRate());
        $this->assertEquals($timestamp, $exchangeRate->getTimestamp());
    }

    /**
     * @test
     */
    public function it_uses_current_timestamp_when_not_provided(): void
    {
        $before = new \DateTimeImmutable();
        $exchangeRate = new ExchangeRate(1.25);
        $after = new \DateTimeImmutable();
        
        $this->assertGreaterThanOrEqual($before, $exchangeRate->getTimestamp());
        $this->assertLessThanOrEqual($after, $exchangeRate->getTimestamp());
    }

    /**
     * @test
     */
    public function it_throws_exception_for_negative_rate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Обменный курс должен быть положительным числом');
        
        new ExchangeRate(-1.25);
    }

    /**
     * @test
     */
    public function it_throws_exception_for_zero_rate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Обменный курс должен быть положительным числом');
        
        new ExchangeRate(0);
    }

    /**
     * @test
     */
    public function it_throws_exception_for_too_high_rate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Обменный курс слишком высокий');
        
        new ExchangeRate(2000000);
    }

    /**
     * @test
     */
    public function it_equals_another_rate_with_same_values(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $rate1 = new ExchangeRate(1.25, $timestamp);
        $rate2 = new ExchangeRate(1.25, $timestamp);
        
        $this->assertTrue($rate1->equals($rate2));
    }

    /**
     * @test
     */
    public function it_not_equals_another_rate_with_different_values(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $rate1 = new ExchangeRate(1.25, $timestamp);
        $rate2 = new ExchangeRate(1.30, $timestamp);
        
        $this->assertFalse($rate1->equals($rate2));
    }

    /**
     * @test
     */
    public function it_creates_from_float(): void
    {
        $exchangeRate = ExchangeRate::fromFloat(1.25);
        
        $this->assertEquals(1.25, $exchangeRate->getRate());
    }

    /**
     * @test
     */
    public function it_creates_from_float_with_timestamp(): void
    {
        $timestamp = new \DateTimeImmutable('2024-08-04 10:00:00');
        $exchangeRate = ExchangeRate::fromFloat(1.25, $timestamp);
        
        $this->assertEquals(1.25, $exchangeRate->getRate());
        $this->assertEquals($timestamp, $exchangeRate->getTimestamp());
    }

    /**
     * @test
     * @dataProvider validRatesProvider
     */
    public function it_accepts_valid_rates(float $rate): void
    {
        $exchangeRate = new ExchangeRate($rate);
        
        $this->assertEquals($rate, $exchangeRate->getRate());
    }

    public function validRatesProvider(): array
    {
        return [
            [0.001],
            [1.0],
            [1.25],
            [100.0],
            [1000.0],
            [999999.99],
        ];
    }
} 