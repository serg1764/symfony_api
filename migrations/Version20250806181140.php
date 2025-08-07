<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250806181140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create log table for DbLogger service';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE log (
            id SERIAL PRIMARY KEY,
            channel VARCHAR(255) NOT NULL,
            level VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            context JSONB,
            created_at TIMESTAMP NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE log');
    }
}
