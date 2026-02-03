<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203165500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add trigram index for aliases column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_schools_aliases_trgm ON schools USING gist ((aliases::text) gist_trgm_ops)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_schools_aliases_trgm');
    }
}
