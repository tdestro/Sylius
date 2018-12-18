<?php declare(strict_types=1);

namespace Sylius\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181122025304 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sylius_locale ADD channel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_locale DROP channel_name');
        $this->addSql('ALTER TABLE sylius_locale ADD CONSTRAINT FK_7BA1286472F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7BA1286472F5A1AA ON sylius_locale (channel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE sylius_locale DROP CONSTRAINT FK_7BA1286472F5A1AA');
        $this->addSql('DROP INDEX IDX_7BA1286472F5A1AA');
        $this->addSql('ALTER TABLE sylius_locale ADD channel_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_locale DROP channel_id');
    }
}
