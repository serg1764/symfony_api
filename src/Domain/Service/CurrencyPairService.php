<?php

namespace App\Domain\Service;

use App\Domain\Entity\CurrencyPair;
use App\Domain\Repository\CurrencyPairRepositoryInterface;
use App\Domain\ValueObject\Currency;
use App\Domain\Event\CurrencyPairAddedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use InvalidArgumentException;

/**
 * Доменный сервис для работы с парами валют
 */
class CurrencyPairService
{
    public function __construct(
        private CurrencyPairRepositoryInterface $currencyPairRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * Добавить новую пару валют для отслеживания
     */
    public function addCurrencyPair(Currency $baseCurrency, Currency $quoteCurrency): CurrencyPair
    {
        // Проверяем, что пары валют не существует
        if ($this->currencyPairRepository->exists($baseCurrency, $quoteCurrency)) {
            throw new InvalidArgumentException(
                sprintf('Пара валют %s/%s уже существует', $baseCurrency->getCode(), $quoteCurrency->getCode())
            );
        }

        // Создаем новую пару валют
        $currencyPair = new CurrencyPair($baseCurrency, $quoteCurrency);
        
        // Сохраняем в репозитории
        $this->currencyPairRepository->save($currencyPair);
        
        // Диспатчим событие
        $this->eventDispatcher->dispatch(new CurrencyPairAddedEvent($currencyPair));
        
        return $currencyPair;
    }

    /**
     * Активировать пару валют
     */
    public function activateCurrencyPair(int $id): CurrencyPair
    {
        $currencyPair = $this->currencyPairRepository->findById($id);
        
        if (!$currencyPair) {
            throw new InvalidArgumentException('Пара валют не найдена!!! - 888 - !!!');
        }
        
        $currencyPair->activate();
        $this->currencyPairRepository->save($currencyPair);
        
        return $currencyPair;
    }

    /**
     * Деактивировать пару валют
     */
    public function deactivateCurrencyPair(int $id): CurrencyPair
    {
        $currencyPair = $this->currencyPairRepository->findById($id);
        
        if (!$currencyPair) {
            throw new InvalidArgumentException('Пара валют не найдена');
        }
        
        $currencyPair->deactivate();
        $this->currencyPairRepository->save($currencyPair);
        
        return $currencyPair;
    }

    /**
     * Удалить пару валют
     */
    public function removeCurrencyPair(int $id): void
    {
        $currencyPair = $this->currencyPairRepository->findById($id);
        
        if (!$currencyPair) {
            throw new InvalidArgumentException('Пара валют не найдена');
        }
        
        $this->currencyPairRepository->remove($currencyPair);
    }

    /**
     * Получить все активные пары валют
     */
    public function getActivePairs(): array
    {
        return $this->currencyPairRepository->findActivePairs();
    }

    /**
     * Проверить существование пары валют
     */
    public function pairExists(Currency $baseCurrency, Currency $quoteCurrency): bool
    {
        return $this->currencyPairRepository->exists($baseCurrency, $quoteCurrency);
    }
} 