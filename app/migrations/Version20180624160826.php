<?php declare(strict_types=1);

namespace Sylius\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180624160826 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE bitbag_cms_block_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_block_image_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_block_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_faq_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_faq_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_page_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_page_image_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_page_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_section_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bitbag_cms_section_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bitbag_cms_block (id INT NOT NULL, code VARCHAR(64) NOT NULL, type VARCHAR(64) NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_321C84CF77153098 ON bitbag_cms_block (code)');
        $this->addSql('CREATE TABLE bitbag_cms_block_sections (block_id INT NOT NULL, section_id INT NOT NULL, PRIMARY KEY(block_id, section_id))');
        $this->addSql('CREATE INDEX IDX_5C95115DE9ED820C ON bitbag_cms_block_sections (block_id)');
        $this->addSql('CREATE INDEX IDX_5C95115DD823E37A ON bitbag_cms_block_sections (section_id)');
        $this->addSql('CREATE TABLE bitbag_cms_block_products (page_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(page_id, product_id))');
        $this->addSql('CREATE INDEX IDX_C4B9089FC4663E4 ON bitbag_cms_block_products (page_id)');
        $this->addSql('CREATE INDEX IDX_C4B9089F4584665A ON bitbag_cms_block_products (product_id)');
        $this->addSql('CREATE TABLE bitbag_cms_block_image (id INT NOT NULL, owner_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D6FD8B297E3C61F9 ON bitbag_cms_block_image (owner_id)');
        $this->addSql('CREATE TABLE bitbag_cms_block_translation (id INT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, content TEXT DEFAULT NULL, link TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_32897FDF2C2AC5D3 ON bitbag_cms_block_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX bitbag_cms_block_translation_uniq_trans ON bitbag_cms_block_translation (translatable_id, locale)');
        $this->addSql('CREATE TABLE bitbag_cms_faq (id INT NOT NULL, code VARCHAR(255) NOT NULL, position INT NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE bitbag_cms_faq_translation (id INT NOT NULL, translatable_id INT NOT NULL, question VARCHAR(1500) NOT NULL, answer TEXT NOT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8B30DD2E2C2AC5D3 ON bitbag_cms_faq_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX bitbag_cms_faq_translation_uniq_trans ON bitbag_cms_faq_translation (translatable_id, locale)');
        $this->addSql('CREATE TABLE bitbag_cms_page (id INT NOT NULL, code VARCHAR(250) NOT NULL, enabled BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_18F07F1B77153098 ON bitbag_cms_page (code)');
        $this->addSql('CREATE TABLE bitbag_cms_page_sections (block_id INT NOT NULL, section_id INT NOT NULL, PRIMARY KEY(block_id, section_id))');
        $this->addSql('CREATE INDEX IDX_D548E347E9ED820C ON bitbag_cms_page_sections (block_id)');
        $this->addSql('CREATE INDEX IDX_D548E347D823E37A ON bitbag_cms_page_sections (section_id)');
        $this->addSql('CREATE TABLE bitbag_cms_page_products (page_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(page_id, product_id))');
        $this->addSql('CREATE INDEX IDX_4D64FA85C4663E4 ON bitbag_cms_page_products (page_id)');
        $this->addSql('CREATE INDEX IDX_4D64FA854584665A ON bitbag_cms_page_products (product_id)');
        $this->addSql('CREATE TABLE bitbag_cms_page_image (id INT NOT NULL, owner_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C9C589EA7E3C61F9 ON bitbag_cms_page_image (owner_id)');
        $this->addSql('CREATE TABLE bitbag_cms_page_translation (id INT NOT NULL, translatable_id INT NOT NULL, slug VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, meta_keywords VARCHAR(1000) DEFAULT NULL, meta_description VARCHAR(2000) DEFAULT NULL, content TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FDD074A62C2AC5D3 ON bitbag_cms_page_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX bitbag_cms_page_translation_uniq_trans ON bitbag_cms_page_translation (translatable_id, locale)');
        $this->addSql('CREATE TABLE bitbag_cms_section (id INT NOT NULL, code VARCHAR(250) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_421D079777153098 ON bitbag_cms_section (code)');
        $this->addSql('CREATE TABLE bitbag_cms_section_translation (id INT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F99CA8582C2AC5D3 ON bitbag_cms_section_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX bitbag_cms_section_translation_uniq_trans ON bitbag_cms_section_translation (translatable_id, locale)');
        $this->addSql('ALTER TABLE bitbag_cms_block_sections ADD CONSTRAINT FK_5C95115DE9ED820C FOREIGN KEY (block_id) REFERENCES bitbag_cms_block (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_block_sections ADD CONSTRAINT FK_5C95115DD823E37A FOREIGN KEY (section_id) REFERENCES bitbag_cms_section (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_block_products ADD CONSTRAINT FK_C4B9089FC4663E4 FOREIGN KEY (page_id) REFERENCES bitbag_cms_block (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_block_products ADD CONSTRAINT FK_C4B9089F4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_block_image ADD CONSTRAINT FK_D6FD8B297E3C61F9 FOREIGN KEY (owner_id) REFERENCES bitbag_cms_block_translation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_block_translation ADD CONSTRAINT FK_32897FDF2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES bitbag_cms_block (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_faq_translation ADD CONSTRAINT FK_8B30DD2E2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES bitbag_cms_faq (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_page_sections ADD CONSTRAINT FK_D548E347E9ED820C FOREIGN KEY (block_id) REFERENCES bitbag_cms_page (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_page_sections ADD CONSTRAINT FK_D548E347D823E37A FOREIGN KEY (section_id) REFERENCES bitbag_cms_section (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_page_products ADD CONSTRAINT FK_4D64FA85C4663E4 FOREIGN KEY (page_id) REFERENCES bitbag_cms_page (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_page_products ADD CONSTRAINT FK_4D64FA854584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_page_image ADD CONSTRAINT FK_C9C589EA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES bitbag_cms_page_translation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_page_translation ADD CONSTRAINT FK_FDD074A62C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES bitbag_cms_page (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bitbag_cms_section_translation ADD CONSTRAINT FK_F99CA8582C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES bitbag_cms_section (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bitbag_cms_block_sections DROP CONSTRAINT FK_5C95115DE9ED820C');
        $this->addSql('ALTER TABLE bitbag_cms_block_products DROP CONSTRAINT FK_C4B9089FC4663E4');
        $this->addSql('ALTER TABLE bitbag_cms_block_translation DROP CONSTRAINT FK_32897FDF2C2AC5D3');
        $this->addSql('ALTER TABLE bitbag_cms_block_image DROP CONSTRAINT FK_D6FD8B297E3C61F9');
        $this->addSql('ALTER TABLE bitbag_cms_faq_translation DROP CONSTRAINT FK_8B30DD2E2C2AC5D3');
        $this->addSql('ALTER TABLE bitbag_cms_page_sections DROP CONSTRAINT FK_D548E347E9ED820C');
        $this->addSql('ALTER TABLE bitbag_cms_page_products DROP CONSTRAINT FK_4D64FA85C4663E4');
        $this->addSql('ALTER TABLE bitbag_cms_page_translation DROP CONSTRAINT FK_FDD074A62C2AC5D3');
        $this->addSql('ALTER TABLE bitbag_cms_page_image DROP CONSTRAINT FK_C9C589EA7E3C61F9');
        $this->addSql('ALTER TABLE bitbag_cms_block_sections DROP CONSTRAINT FK_5C95115DD823E37A');
        $this->addSql('ALTER TABLE bitbag_cms_page_sections DROP CONSTRAINT FK_D548E347D823E37A');
        $this->addSql('ALTER TABLE bitbag_cms_section_translation DROP CONSTRAINT FK_F99CA8582C2AC5D3');
        $this->addSql('DROP SEQUENCE bitbag_cms_block_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_block_image_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_block_translation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_faq_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_faq_translation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_page_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_page_image_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_page_translation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_section_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bitbag_cms_section_translation_id_seq CASCADE');
        $this->addSql('DROP TABLE bitbag_cms_block');
        $this->addSql('DROP TABLE bitbag_cms_block_sections');
        $this->addSql('DROP TABLE bitbag_cms_block_products');
        $this->addSql('DROP TABLE bitbag_cms_block_image');
        $this->addSql('DROP TABLE bitbag_cms_block_translation');
        $this->addSql('DROP TABLE bitbag_cms_faq');
        $this->addSql('DROP TABLE bitbag_cms_faq_translation');
        $this->addSql('DROP TABLE bitbag_cms_page');
        $this->addSql('DROP TABLE bitbag_cms_page_sections');
        $this->addSql('DROP TABLE bitbag_cms_page_products');
        $this->addSql('DROP TABLE bitbag_cms_page_image');
        $this->addSql('DROP TABLE bitbag_cms_page_translation');
        $this->addSql('DROP TABLE bitbag_cms_section');
        $this->addSql('DROP TABLE bitbag_cms_section_translation');
    }
}
