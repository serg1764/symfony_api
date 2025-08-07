<?php

namespace App\Tests\Application\Command;

use App\Application\DTO\AddCurrencyPairCommand;
use App\Application\Handler\AddCurrencyPairHandler;
use App\Application\Command\AddCurrencyPairConsoleCommand;
use App\Domain\Entity\CurrencyPair;
use App\Domain\ValueObject\Currency;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Тесты для консольной команды AddCurrencyPairConsoleCommand
 */
class AddCurrencyPairConsoleCommandTest extends TestCase
{
    private AddCurrencyPairHandler $handler;
    private AddCurrencyPairConsoleCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(AddCurrencyPairHandler::class);
        $this->command = new AddCurrencyPairConsoleCommand($this->handler);
        
        $application = new Application();
        $application->add($this->command);
        
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * @test
     */
    public function it_adds_currency_pair_successfully(): void
    {
        $baseCurrency = new Currency('USD');
        $quoteCurrency = new Currency('EUR');
        
        $currencyPair = $this->createMock(CurrencyPair::class);
        $currencyPair->method('getId')->willReturn(1);
        $currencyPair->method('getBaseCurrency')->willReturn($baseCurrency);
        $currencyPair->method('getQuoteCurrency')->willReturn($quoteCurrency);

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(AddCurrencyPairCommand::class))
            ->willReturn($currencyPair);

        $this->commandTester->execute([
            'base-currency' => 'USD',
            'quote-currency' => 'EUR',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('успешно добавлена', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_handles_invalid_currency_error(): void
    {
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->willThrowException(new \InvalidArgumentException('Неподдерживаемая валюта: INVALID'));

        $this->commandTester->execute([
            'base-currency' => 'INVALID',
            'quote-currency' => 'EUR',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Неподдерживаемая валюта', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_handles_general_exception(): void
    {
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->willThrowException(new \Exception('Database connection failed'));

        $this->commandTester->execute([
            'base-currency' => 'USD',
            'quote-currency' => 'EUR',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Произошла ошибка', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_displays_help_information(): void
    {
        $this->commandTester->execute(['--help']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Добавить новую пару валют', $output);
        $this->assertStringContainsString('base-currency', $output);
        $this->assertStringContainsString('quote-currency', $output);
    }
} 