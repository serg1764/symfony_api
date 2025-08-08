<?php

namespace App\Tests\Domain\Service;

use App\Domain\Entity\CurrencyPair;
use App\Domain\Event\CurrencyPairAddedEvent;
use App\Domain\Repository\CurrencyPairRepositoryInterface;
use App\Domain\Service\CurrencyPairService;
use App\Domain\ValueObject\Currency;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Тесты для Domain Service CurrencyPairService
 */
class CurrencyPairServiceTest extends TestCase
{
    private CurrencyPairRepositoryInterface|MockObject $repository;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private CurrencyPairService $service;

    /**
     * Настройка тестового окружения перед каждым тестом
     */
    protected function setUp(): void
    {
        $this->repository = $this->createMock(CurrencyPairRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->service = new CurrencyPairService($this->repository, $this->eventDispatcher);
    }

    /**
     * Проверяет добавление новой валютной пары
     */
    public function testItAddsNewCurrencyPair(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->with($baseCurrency, $quoteCurrency)
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(CurrencyPair::class));

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CurrencyPairAddedEvent::class));

        $currencyPair = $this->service->addCurrencyPair($baseCurrency, $quoteCurrency);

        $this->assertInstanceOf(CurrencyPair::class, $currencyPair);
        $this->assertTrue($currencyPair->isActive());
    }

    /**
     * Проверяет выброс исключения при существующей паре валют
     */
    public function testItThrowsExceptionWhenPairAlreadyExists(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->with($baseCurrency, $quoteCurrency)
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пара валют USD/EUR уже существует');

        $this->service->addCurrencyPair($baseCurrency, $quoteCurrency);
    }

    /**
     * Проверяет активацию валютной пары
     */
    public function testItActivatesCurrencyPair(): void
    {
        $currencyPair = $this->createMock(CurrencyPair::class);
        $currencyPair->expects($this->once())->method('activate');

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($currencyPair);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($currencyPair);

        $result = $this->service->activateCurrencyPair(1);

        $this->assertSame($currencyPair, $result);
    }

    /**
     * Проверяет выброс исключения при активации несуществующей пары
     */
    public function testItThrowsExceptionWhenActivatingNonexistentPair(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пара валют не найдена');

        $this->service->activateCurrencyPair(999);
    }

    /**
     * Проверяет деактивацию валютной пары
     */
    public function testItDeactivatesCurrencyPair(): void
    {
        $currencyPair = $this->createMock(CurrencyPair::class);
        $currencyPair->expects($this->once())->method('deactivate');

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($currencyPair);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($currencyPair);

        $result = $this->service->deactivateCurrencyPair(1);

        $this->assertSame($currencyPair, $result);
    }

    /**
     * Проверяет удаление валютной пары
     */
    public function testItRemovesCurrencyPair(): void
    {
        $currencyPair = $this->createMock(CurrencyPair::class);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($currencyPair);

        $this->repository
            ->expects($this->once())
            ->method('remove')
            ->with($currencyPair);

        $this->service->removeCurrencyPair(1);
    }

    /**
     * Проверяет возврат активных пар валют
     */
    public function testItReturnsActivePairs(): void
    {
        $expectedPairs = [
            $this->createMock(CurrencyPair::class),
            $this->createMock(CurrencyPair::class),
        ];

        $this->repository
            ->expects($this->once())
            ->method('findActivePairs')
            ->willReturn($expectedPairs);

        $result = $this->service->getActivePairs();

        $this->assertSame($expectedPairs, $result);
    }

    /**
     * Проверяет проверку существования пары валют
     */
    public function testItChecksPairExistence(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->with($baseCurrency, $quoteCurrency)
            ->willReturn(true);

        $result = $this->service->pairExists($baseCurrency, $quoteCurrency);

        $this->assertTrue($result);
    }
} 