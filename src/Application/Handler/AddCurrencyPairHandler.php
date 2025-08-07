<?php

namespace App\Application\Handler;

use App\Application\DTO\AddCurrencyPairCommand;
use App\Domain\Entity\CurrencyPair;
use App\Domain\Service\CurrencyPairService;
use Psr\Log\LoggerInterface;

/**
 * Обработчик команды добавления пары валют
 */
class AddCurrencyPairHandler
{
    public function __construct(
        private CurrencyPairService $currencyPairService,
        private LoggerInterface $logger
    ) {}

    public function handle(AddCurrencyPairCommand $command): CurrencyPair
    {
        $baseCurrency = $command->getBaseCurrency();
        $quoteCurrency = $command->getQuoteCurrency();

        $this->logger->info('Добавление новой пары валют', [
            'base_currency' => $baseCurrency->getCode(),
            'quote_currency' => $quoteCurrency->getCode()
        ]);

        $currencyPair = $this->currencyPairService->addCurrencyPair($baseCurrency, $quoteCurrency);

        $this->logger->info('Пара валют успешно добавлена', [
            'pair_id' => $currencyPair->getId(),
            'pair_code' => $currencyPair->getPairCode()
        ]);

        return $currencyPair;
    }
} 