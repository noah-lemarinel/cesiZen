<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Remove isPublished field from BlogPost
 */
final class Version20260521095213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove isPublished column from blog_post table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_post DROP COLUMN is_published');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_post ADD COLUMN is_published BOOLEAN DEFAULT true NOT NULL');
    }
}

