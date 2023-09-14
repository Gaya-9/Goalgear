<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230726110742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD commandes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B38BF5C2E6 FOREIGN KEY (commandes_id) REFERENCES commandes (id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B38BF5C2E6 ON utilisateur (commandes_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B38BF5C2E6');
        $this->addSql('DROP INDEX IDX_1D1C63B38BF5C2E6 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP commandes_id');
    }
}
