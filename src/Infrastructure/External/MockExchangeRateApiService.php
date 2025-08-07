<?php

namespace App\Infrastructure\External;

use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use App\Service\DbLoggerInterface;

/**
 * Mock сервис для тестирования и разработки
 * Используется только в тестовой среде
 */
class MockExchangeRateApiService implements ExchangeRateApiInterface
{
    private const MOCK_RATES = [
        'USDEUR' => 0.86,
        'EURUSD' => 1.18,
        'USDGBP' => 0.73,
        'GBPUSD' => 1.37,
        'USDJPY' => 110.50,
        'JPYUSD' => 0.009,
        'USDCAD' => 1.25,
        'CADUSD' => 0.80,
        'USDAUD' => 1.35,
        'AUDUSD' => 0.74,
        'USDCHF' => 0.92,
        'CHFUSD' => 1.09,
        'USDCNY' => 6.45,
        'CNYUSD' => 0.155,
        'USDRUB' => 75.50,
        'RUBUSD' => 0.013,
    ];

    public function __construct(
        private ?DbLoggerInterface $dbLogger = null
    ) {}

    public function getExchangeRate(Currency $baseCurrency, Currency $quoteCurrency): ExchangeRate
    {
        $pairCode = $baseCurrency->getCode() . $quoteCurrency->getCode();
        
        $this->dbLogger?->log('mock_api', 'info', 'Получен mock курс валют', [
            'base_currency' => $baseCurrency->getCode(),
            'quote_currency' => $quoteCurrency->getCode(),
            'pair_code' => $pairCode
        ]);
        
        if (isset(self::MOCK_RATES[$pairCode])) {
            return new ExchangeRate(self::MOCK_RATES[$pairCode]);
        }

        // Если нет готового курса, генерируем случайный в разумных пределах
        $rate = 0.5 + (mt_rand() / mt_getrandmax()) * 2.0;
        
        return new ExchangeRate($rate);
    }

    public function getExchangeRates(array $currencyPairs): array
    {
        $rates = [];
        
        foreach ($currencyPairs as $pair) {
            $baseCurrency = $pair['base'];
            $quoteCurrency = $pair['quote'];
            $rates[] = [
                'base' => $baseCurrency,
                'quote' => $quoteCurrency,
                'rate' => $this->getExchangeRate($baseCurrency, $quoteCurrency)
            ];
        }
        
        return $rates;
    }

    public function isAvailable(): bool
    {
        return true; // Mock сервис всегда доступен
    }
}
