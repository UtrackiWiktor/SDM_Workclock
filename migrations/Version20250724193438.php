<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250724193438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create a project item table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                CREATE TABLE workclock.project_item (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL
                )
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE workclock.clock_entry 
                    ADD COLUMN project_id INTEGER,
                    ADD CONSTRAINT fk_clock_entry_project
                        FOREIGN KEY (project_id) REFERENCES workclock.project_item(id)
                        ON DELETE SET NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
