<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260509000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user, emotion, and emotion_entry tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(100) DEFAULT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, is_admin BOOLEAN NOT NULL DEFAULT 0)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');

        $this->addSql('CREATE TABLE emotion (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, color VARCHAR(7) DEFAULT NULL)');

        $this->addSql('CREATE TABLE emotion_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, emotion_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, notes CLOB DEFAULT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (emotion_id) REFERENCES emotion (id), FOREIGN KEY (user_id) REFERENCES user (id))');
        $this->addSql('CREATE INDEX IDX_87FF1C50D5E258FF ON emotion_entry (emotion_id)');
        $this->addSql('CREATE INDEX IDX_87FF1C50A76ED395 ON emotion_entry (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE emotion_entry');
        $this->addSql('DROP TABLE emotion');
        $this->addSql('DROP TABLE user');
    }
}

