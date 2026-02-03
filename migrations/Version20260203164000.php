<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203164000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enable pg_trgm extension and add trigram index for optimized fuzzy search';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        $this->addSql('CREATE INDEX idx_schools_name_trgm ON schools USING gist (name gist_trgm_ops)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_schools_name_trgm');
        $this->addSql('DROP EXTENSION pg_trgm');
    }
}
