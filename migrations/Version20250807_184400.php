<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавление таблиц для пар RUB/USD и USD/RUB
 */
final class Version20250807_184400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление таблиц exchange_rate_rub_usd и exchange_rate_usd_rub для пар RUB/USD и USD/RUB';
    }

    public function up(Schema $schema): void
    {
        // Таблица для пары RUB/USD
        $this->addSql('CREATE TABLE exchange_rate_rub_usd (
            id SERIAL PRIMARY KEY,
            rate DECIMAL(20,12) NOT NULL,
            timestamp TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL
        )');
        $this->addSql('CREATE INDEX idx_rub_usd_timestamp ON exchange_rate_rub_usd (timestamp)');

        // Таблица для пары USD/RUB
        $this->addSql('CREATE TABLE exchange_rate_usd_rub (
            id SERIAL PRIMARY KEY,
            rate DECIMAL(20,12) NOT NULL,
            timestamp TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL
        )');
        $this->addSql('CREATE INDEX idx_usd_rub_timestamp ON exchange_rate_usd_rub (timestamp)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE exchange_rate_usd_rub');
        $this->addSql('DROP TABLE exchange_rate_rub_usd');
    }
}
