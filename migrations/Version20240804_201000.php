<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Миграция для создания таблиц валютных пар и отдельных таблиц для каждой пары
 */
final class Version20240804_201000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблиц для системы трекинга валютных курсов с отдельными таблицами для каждой пары';
    }

    public function up(Schema $schema): void
    {
        // Таблица валютных пар
        $this->addSql('CREATE TABLE currency_pairs (
            id SERIAL PRIMARY KEY,
            base_currency VARCHAR(3) NOT NULL,
            quote_currency VARCHAR(3) NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT true,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        )');
        
        $this->addSql('CREATE INDEX idx_currency_pair ON currency_pairs (base_currency, quote_currency)');
        $this->addSql('CREATE INDEX idx_currency_pairs_active ON currency_pairs (is_active)');

        // Таблица для пары USD/EUR
        $this->addSql('CREATE TABLE exchange_rate_usd_eur (
            id SERIAL PRIMARY KEY,
            rate DECIMAL(20,12) NOT NULL,
            timestamp TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL
        )');
        $this->addSql('CREATE INDEX idx_usd_eur_timestamp ON exchange_rate_usd_eur (timestamp)');

        // Таблица для пары EUR/USD
        $this->addSql('CREATE TABLE exchange_rate_eur_usd (
            id SERIAL PRIMARY KEY,
            rate DECIMAL(20,12) NOT NULL,
            timestamp TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL
        )');
        $this->addSql('CREATE INDEX idx_eur_usd_timestamp ON exchange_rate_eur_usd (timestamp)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE exchange_rate_eur_usd');
        $this->addSql('DROP TABLE exchange_rate_usd_eur');
        $this->addSql('DROP TABLE currency_pairs');
    }
} 