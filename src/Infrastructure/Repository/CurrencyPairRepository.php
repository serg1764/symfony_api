<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\CurrencyPair;
use App\Domain\Repository\CurrencyPairRepositoryInterface;
use App\Domain\ValueObject\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Реализация репозитория для работы с парами валют
 */
class CurrencyPairRepository implements CurrencyPairRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(CurrencyPair::class);
    }

    public function findById(int $id): ?CurrencyPair
    {
        return $this->repository->find($id);
    }

    public function findByCurrencies(Currency $baseCurrency, Currency $quoteCurrency): ?CurrencyPair
    {
        return $this->repository->findOneBy([
            'baseCurrency' => $baseCurrency->getCode(),
            'quoteCurrency' => $quoteCurrency->getCode()
        ]);
    }

    public function findActivePairs(): array
    {
        return $this->repository->findBy(['isActive' => true], ['createdAt' => 'DESC']);
    }

    public function save(CurrencyPair $currencyPair): void
    {
        $this->entityManager->persist($currencyPair);
        $this->entityManager->flush();
    }

    public function remove(CurrencyPair $currencyPair): void
    {
        $this->entityManager->remove($currencyPair);
        $this->entityManager->flush();
    }

    public function exists(Currency $baseCurrency, Currency $quoteCurrency): bool
    {
        $count = $this->repository->count([
            'baseCurrency' => $baseCurrency->getCode(),
            'quoteCurrency' => $quoteCurrency->getCode()
        ]);

        return $count > 0;
    }
} 