<?php

namespace App\Infrastructure\External;

use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\ExchangeRate;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\DbLoggerInterface;
use RuntimeException;

/**
 * Реализация API сервиса для FreeCurrencyAPI
 */
class FreeCurrencyApiService implements ExchangeRateApiInterface
{
    private const API_BASE_URL = 'https://api.freecurrencyapi.com/v1/latest';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey = '',
        private ?DbLoggerInterface $dbLogger = null
    ) {}

    public function getExchangeRate(Currency $baseCurrency, Currency $quoteCurrency): ExchangeRate
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('API ключ не настроен. Установите FREECURRENCY_API_KEY в .env.local');
        }

        try {
            $response = $this->httpClient->request('GET', self::API_BASE_URL, [
                'query' => [
                    'apikey' => $this->apiKey,
                    'base_currency' => $baseCurrency->getCode(),
                    'currencies' => $quoteCurrency->getCode()
                ]
            ]);

            $data = $response->toArray();

            // Логируем полученные данные от API
            $this->dbLogger?->log('api', 'info', 'Получены данные от FreeCurrencyAPI', [
                'base_currency' => $baseCurrency->getCode(),
                'quote_currency' => $quoteCurrency->getCode(),
                'api_response' => $data,
                'response_status' => $response->getStatusCode(),
                'response_headers' => $response->getHeaders()
            ]);

            // Дополнительное логирование через ORM
            $this->dbLogger?->logWithOrm('api', 'info', 'Получены данные от FreeCurrencyAPI (ORM)', [
                'base_currency' => $baseCurrency->getCode(),
                'quote_currency' => $quoteCurrency->getCode(),
                'api_response' => $data,
                'response_status' => $response->getStatusCode(),
                'response_headers' => $response->getHeaders()
            ]);
            
            if (!isset($data['data'][$quoteCurrency->getCode()])) {
                throw new RuntimeException('Неверный ответ от API');
            }

            $rate = $data['data'][$quoteCurrency->getCode()];
            
            return new ExchangeRate($rate);
        } catch (\Exception $e) {
            $this->dbLogger?->log('api', 'error', 'Ошибка при получении курса валют', [
                'base_currency' => $baseCurrency->getCode(),
                'quote_currency' => $quoteCurrency->getCode(),
                'error' => $e->getMessage()
            ]);

            throw new RuntimeException(
                sprintf('Не удалось получить курс валют %s/%s: %s', 
                    $baseCurrency->getCode(), 
                    $quoteCurrency->getCode(), 
                    $e->getMessage()
                )
            );
        }
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
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = $this->httpClient->request('GET', self::API_BASE_URL, [
                'query' => [
                    'apikey' => $this->apiKey,
                    'base_currency' => 'USD',
                    'currencies' => 'EUR'
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
} 