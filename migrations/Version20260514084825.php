<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260514084825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__emotion AS SELECT id, name, description, color FROM emotion');
        $this->addSql('DROP TABLE emotion');
        $this->addSql('CREATE TABLE emotion (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, color VARCHAR(7) DEFAULT NULL, parent_id INTEGER DEFAULT NULL, CONSTRAINT FK_DEBC77727ACA70 FOREIGN KEY (parent_id) REFERENCES emotion (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO emotion (id, name, description, color) SELECT id, name, description, color FROM __temp__emotion');
        $this->addSql('DROP TABLE __temp__emotion');
        $this->addSql('CREATE INDEX IDX_DEBC77727ACA70 ON emotion (parent_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__emotion_entry AS SELECT id, emotion_id, user_id, notes, created_at FROM emotion_entry');
        $this->addSql('DROP TABLE emotion_entry');
        $this->addSql('CREATE TABLE emotion_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, emotion_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, notes CLOB DEFAULT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (emotion_id) REFERENCES emotion (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO emotion_entry (id, emotion_id, user_id, notes, created_at) SELECT id, emotion_id, user_id, notes, created_at FROM __temp__emotion_entry');
        $this->addSql('DROP TABLE __temp__emotion_entry');
        $this->addSql('CREATE INDEX IDX_A482672A1EE4A582 ON emotion_entry (emotion_id)');
        $this->addSql('CREATE INDEX IDX_A482672AA76ED395 ON emotion_entry (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, name, roles, password, is_admin FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(100) DEFAULT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, is_admin BOOLEAN DEFAULT 0 NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, name, roles, password, is_admin) SELECT id, email, name, roles, password, is_admin FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__emotion AS SELECT id, name, description, color FROM emotion');
        $this->addSql('DROP TABLE emotion');
        $this->addSql('CREATE TABLE emotion (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, color VARCHAR(7) DEFAULT NULL)');
        $this->addSql('INSERT INTO emotion (id, name, description, color) SELECT id, name, description, color FROM __temp__emotion');
        $this->addSql('DROP TABLE __temp__emotion');
        $this->addSql('CREATE TEMPORARY TABLE __temp__emotion_entry AS SELECT id, notes, created_at, emotion_id, user_id FROM emotion_entry');
        $this->addSql('DROP TABLE emotion_entry');
        $this->addSql('CREATE TABLE emotion_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, notes CLOB DEFAULT NULL, created_at DATETIME NOT NULL, emotion_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_A482672A1EE4A582 FOREIGN KEY (emotion_id) REFERENCES emotion (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A482672AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO emotion_entry (id, notes, created_at, emotion_id, user_id) SELECT id, notes, created_at, emotion_id, user_id FROM __temp__emotion_entry');
        $this->addSql('DROP TABLE __temp__emotion_entry');
        $this->addSql('CREATE INDEX IDX_87FF1C50A76ED395 ON emotion_entry (user_id)');
        $this->addSql('CREATE INDEX IDX_87FF1C50D5E258FF ON emotion_entry (emotion_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, name, roles, password, is_admin FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(100) DEFAULT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, is_admin BOOLEAN DEFAULT 0 NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, name, roles, password, is_admin) SELECT id, email, name, roles, password, is_admin FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
