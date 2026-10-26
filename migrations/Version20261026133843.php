<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261026133843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY `FK_A45BDDC1EFF67ABC`');
        $this->addSql('DROP INDEX IDX_A45BDDC1EFF67ABC ON application');
        $this->addSql('ALTER TABLE application DROP application_status_id');
        $this->addSql('ALTER TABLE status DROP FOREIGN KEY `FK_7B00651CBE4BA557`');
        $this->addSql('DROP INDEX IDX_7B00651CBE4BA557 ON status');
        $this->addSql('ALTER TABLE status DROP status_application_id');
        $this->addSql('ALTER TABLE status_application ADD application_id INT NOT NULL, ADD status_id INT NOT NULL');
        $this->addSql('ALTER TABLE status_application ADD CONSTRAINT FK_A081A8303E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
        $this->addSql('ALTER TABLE status_application ADD CONSTRAINT FK_A081A8306BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('CREATE INDEX IDX_A081A8303E030ACD ON status_application (application_id)');
        $this->addSql('CREATE INDEX IDX_A081A8306BF700BD ON status_application (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application ADD application_status_id INT NOT NULL');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT `FK_A45BDDC1EFF67ABC` FOREIGN KEY (application_status_id) REFERENCES status_application (id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC1EFF67ABC ON application (application_status_id)');
        $this->addSql('ALTER TABLE status ADD status_application_id INT NOT NULL');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT `FK_7B00651CBE4BA557` FOREIGN KEY (status_application_id) REFERENCES status_application (id)');
        $this->addSql('CREATE INDEX IDX_7B00651CBE4BA557 ON status (status_application_id)');
        $this->addSql('ALTER TABLE status_application DROP FOREIGN KEY FK_A081A8303E030ACD');
        $this->addSql('ALTER TABLE status_application DROP FOREIGN KEY FK_A081A8306BF700BD');
        $this->addSql('DROP INDEX IDX_A081A8303E030ACD ON status_application');
        $this->addSql('DROP INDEX IDX_A081A8306BF700BD ON status_application');
        $this->addSql('ALTER TABLE status_application DROP application_id, DROP status_id');
    }
}
