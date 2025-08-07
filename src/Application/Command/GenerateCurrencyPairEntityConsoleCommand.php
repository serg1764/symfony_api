<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\ValueObject\Currency;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:generate-currency-pair-entity',
    description: 'Generate entity class for a new currency pair'
)]
class GenerateCurrencyPairEntityConsoleCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('base-currency', InputArgument::REQUIRED, 'Base currency code (e.g., USD)')
             ->addArgument('quote-currency', InputArgument::REQUIRED, 'Quote currency code (e.g., EUR)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseCurrencyCode = strtoupper($input->getArgument('base-currency'));
        $quoteCurrencyCode = strtoupper($input->getArgument('quote-currency'));

        try {
            $baseCurrency = new Currency($baseCurrencyCode);
            $quoteCurrency = new Currency($quoteCurrencyCode);
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('❌ Error: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        $pairCode = $baseCurrencyCode . $quoteCurrencyCode;
        $className = $pairCode;
        $tableName = 'exchange_rate_' . strtolower($baseCurrencyCode) . '_' . strtolower($quoteCurrencyCode);

        $output->writeln(sprintf('🔄 Generating entity for currency pair %s/%s', $baseCurrencyCode, $quoteCurrencyCode));

        // Создаем файл сущности
        $entityContent = $this->generateEntityContent($className, $tableName, $baseCurrencyCode, $quoteCurrencyCode);
        $entityPath = sprintf('src/Domain/Entity/ExchangeRate/%s.php', $className);

        if (file_exists($entityPath)) {
            $output->writeln(sprintf('⚠️  Entity file already exists: %s', $entityPath));
            return Command::SUCCESS;
        }

        if (!is_dir(dirname($entityPath))) {
            mkdir(dirname($entityPath), 0755, true);
        }

        file_put_contents($entityPath, $entityContent);

        $output->writeln(sprintf('✅ Entity created: %s', $entityPath));
        $output->writeln('');
        $output->writeln('📝 Next steps:');
        $output->writeln(sprintf('1. Add "%s" => %s::class to ExchangeRateEntityFactory::ENTITY_MAP', $pairCode, $className));
        $output->writeln('2. Create migration for the new table');
        $output->writeln('3. Update tests');

        return Command::SUCCESS;
    }

    private function generateEntityContent(string $className, string $tableName, string $baseCurrency, string $quoteCurrency): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Domain\Entity\ExchangeRate;

use App\Domain\Entity\ExchangeRateRecord;
use App\Domain\ValueObject\ExchangeRate;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность для хранения истории курса {$baseCurrency}/{$quoteCurrency}
 */
#[ORM\Entity]
#[ORM\Table(name: '{$tableName}')]
#[ORM\Index(columns: ['timestamp'], name: 'idx_{$tableName}_timestamp')]
class {$className} extends ExchangeRateRecord
{
    public function __construct(ExchangeRate \$exchangeRate)
    {
        parent::__construct(\$exchangeRate);
    }

    public static function getTableName(): string
    {
        return '{$tableName}';
    }
}
PHP;
    }
} 