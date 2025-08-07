<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Value Object для представления валюты
 */
final class Currency implements \JsonSerializable
{
    private const SUPPORTED_CURRENCIES = [
        'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'RUB', 'INR',
        'BRL', 'MXN', 'KRW', 'SGD', 'HKD', 'NZD', 'SEK', 'NOK', 'DKK', 'PLN'
    ];

    private string $code;

    public function __construct(string $code)
    {
        $this->validate($code);
        $this->code = strtoupper($code);
    }

    private function validate(string $code): void
    {
        if (empty($code)) {
            throw new InvalidArgumentException('Код валюты не может быть пустым');
        }

        if (!in_array(strtoupper($code), self::SUPPORTED_CURRENCIES)) {
            throw new InvalidArgumentException(
                sprintf('Неподдерживаемая валюта: %s. Поддерживаемые валюты: %s', 
                    $code, 
                    implode(', ', self::SUPPORTED_CURRENCIES)
                )
            );
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(Currency $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'code' => $this->code
        ];
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public static function getSupportedCurrencies(): array
    {
        return self::SUPPORTED_CURRENCIES;
    }
} 