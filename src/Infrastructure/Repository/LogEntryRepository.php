<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\LogEntry;
use App\Domain\Repository\LogEntryRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Реализация репозитория для работы с логами
 */
class LogEntryRepository extends ServiceEntityRepository implements LogEntryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntry::class);
    }

    public function save(LogEntry $logEntry): void
    {
        $this->_em->persist($logEntry);
        $this->_em->flush();
    }

    public function findByChannel(string $channel): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.channel = :channel')
            ->setParameter('channel', $channel)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByLevel(string $level): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.level = :level')
            ->setParameter('level', $level)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.createdAt >= :from')
            ->andWhere('l.createdAt <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByChannelAndLevel(string $channel, string $level): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.channel = :channel')
            ->andWhere('l.level = :level')
            ->setParameter('channel', $channel)
            ->setParameter('level', $level)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatest(int $limit = 100): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function deleteOldRecords(\DateTimeImmutable $olderThan): int
    {
        return $this->createQueryBuilder('l')
            ->delete()
            ->where('l.createdAt < :olderThan')
            ->setParameter('olderThan', $olderThan)
            ->getQuery()
            ->execute();
    }
} 