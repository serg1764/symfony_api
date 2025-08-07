<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавление таблицы для пары USD/GBP
 */
final class Version20250806181200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление таблицы exchange_rate_usd_gbp для пары USD/GBP';
    }

    public function up(Schema $schema): void
    {
        // Таблица для пары USD/GBP
        $this->addSql('CREATE TABLE exchange_rate_usd_gbp (
            id SERIAL PRIMARY KEY,
            rate DECIMAL(20,12) NOT NULL,
            timestamp TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL
        )');
        $this->addSql('CREATE INDEX idx_usd_gbp_timestamp ON exchange_rate_usd_gbp (timestamp)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE exchange_rate_usd_gbp');
    }
} 