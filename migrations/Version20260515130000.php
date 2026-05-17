<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260515130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add createdBy field to breathing_exercise table for user-created exercises';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $db = $this->connection->getDatabasePlatform()::class;

        if (false !== strpos($db, 'SQLite')) {
            // SQLite requires recreating the table to add a new column with foreign key
            $this->addSql('ALTER TABLE breathing_exercise ADD COLUMN created_by_id INTEGER DEFAULT NULL');
            $this->addSql('CREATE INDEX IDX_42A4FF7CB03A8386 ON breathing_exercise (created_by_id)');
        } else {
            $this->addSql('ALTER TABLE breathing_exercise ADD created_by_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE breathing_exercise ADD CONSTRAINT FK_42A4FF7CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE CASCADE');
            $this->addSql('CREATE INDEX IDX_42A4FF7CB03A8386 ON breathing_exercise (created_by_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $db = $this->connection->getDatabasePlatform()::class;

        if (false === strpos($db, 'SQLite')) {
            $this->addSql('ALTER TABLE breathing_exercise DROP FOREIGN KEY FK_42A4FF7CB03A8386');
        }

        $this->addSql('DROP INDEX IDX_42A4FF7CB03A8386');
        $this->addSql('ALTER TABLE breathing_exercise DROP COLUMN created_by_id');
    }
}
