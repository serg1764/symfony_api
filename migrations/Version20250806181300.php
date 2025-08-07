<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавление таблицы для пары GBP/USD
 */
final class Version20250806181300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление таблицы exchange_rate_gbp_usd для пары GBP/USD';
    }

    public function up(Schema $schema): void
    {
        // Таблица для пары GBP/USD
        $this->addSql('CREATE TABLE exchange_rate_gbp_usd (
            id SERIAL PRIMARY KEY,
            rate DECIMAL(20,12) NOT NULL,
            timestamp TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL
        )');
        $this->addSql('CREATE INDEX idx_gbp_usd_timestamp ON exchange_rate_gbp_usd (timestamp)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE exchange_rate_gbp_usd');
    }
} 