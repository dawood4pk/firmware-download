<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create software_version table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE software_version (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            system_version VARCHAR(100) NOT NULL,
            system_version_alt VARCHAR(100) NOT NULL,
            link VARCHAR(500) DEFAULT NULL,
            st VARCHAR(500) DEFAULT NULL,
            gd VARCHAR(500) DEFAULT NULL,
            is_latest BOOLEAN NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )');

        $this->addSql('CREATE INDEX idx_software_version_alt ON software_version (system_version_alt)');

        $this->addSql('CREATE TABLE admin_user (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            username VARCHAR(180) NOT NULL,
            roles CLOB NOT NULL,
            password VARCHAR(255) NOT NULL
        )');

        $this->addSql('CREATE UNIQUE INDEX uniq_admin_user_username ON admin_user (username)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE software_version');
        $this->addSql('DROP TABLE admin_user');
    }
}
