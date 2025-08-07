<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Создание таблиц для очередей сообщений Messenger
 */
final class Version20250807_184300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблиц messenger_messages для очередей async_fetch и async_save';
    }

    public function up(Schema $schema): void
    {
        // Таблица для очереди async_fetch
        $this->addSql('CREATE TABLE messenger_messages (
            id BIGSERIAL PRIMARY KEY,
            body TEXT NOT NULL,
            headers TEXT NOT NULL,
            queue_name VARCHAR(190) NOT NULL,
            created_at TIMESTAMP NOT NULL,
            available_at TIMESTAMP NOT NULL,
            delivered_at TIMESTAMP DEFAULT NULL
        )');
        
        $this->addSql('CREATE INDEX idx_messenger_messages_queue_name ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX idx_messenger_messages_available_at ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX idx_messenger_messages_delivered_at ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE messenger_messages');
    }
}
