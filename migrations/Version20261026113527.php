<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261026113527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE status_application_status (status_application_id INT NOT NULL, status_id INT NOT NULL, INDEX IDX_1EF23469BE4BA557 (status_application_id), INDEX IDX_1EF234696BF700BD (status_id), PRIMARY KEY (status_application_id, status_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE status_application_application (status_application_id INT NOT NULL, application_id INT NOT NULL, INDEX IDX_4812E2E5BE4BA557 (status_application_id), INDEX IDX_4812E2E53E030ACD (application_id), PRIMARY KEY (status_application_id, application_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE status_application_status ADD CONSTRAINT FK_1EF23469BE4BA557 FOREIGN KEY (status_application_id) REFERENCES status_application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE status_application_status ADD CONSTRAINT FK_1EF234696BF700BD FOREIGN KEY (status_id) REFERENCES status (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE status_application_application ADD CONSTRAINT FK_4812E2E5BE4BA557 FOREIGN KEY (status_application_id) REFERENCES status_application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE status_application_application ADD CONSTRAINT FK_4812E2E53E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application ADD user_id INT NOT NULL, ADD company_id INT NOT NULL, ADD offer_id INT DEFAULT NULL, ADD application_method_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC153C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC19D894366 FOREIGN KEY (application_method_id) REFERENCES application_method (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A45BDDC153C674EE ON application (offer_id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC1A76ED395 ON application (user_id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC1979B1AD6 ON application (company_id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC19D894366 ON application (application_method_id)');
        $this->addSql('ALTER TABLE offer ADD company_id INT NOT NULL');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_29D6873E979B1AD6 ON offer (company_id)');
        $this->addSql('ALTER TABLE reminder ADD user_id INT NOT NULL, ADD application_id INT NOT NULL');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F403E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
        $this->addSql('CREATE INDEX IDX_40374F40A76ED395 ON reminder (user_id)');
        $this->addSql('CREATE INDEX IDX_40374F403E030ACD ON reminder (application_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status_application_status DROP FOREIGN KEY FK_1EF23469BE4BA557');
        $this->addSql('ALTER TABLE status_application_status DROP FOREIGN KEY FK_1EF234696BF700BD');
        $this->addSql('ALTER TABLE status_application_application DROP FOREIGN KEY FK_4812E2E5BE4BA557');
        $this->addSql('ALTER TABLE status_application_application DROP FOREIGN KEY FK_4812E2E53E030ACD');
        $this->addSql('DROP TABLE status_application_status');
        $this->addSql('DROP TABLE status_application_application');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1A76ED395');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1979B1AD6');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC153C674EE');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC19D894366');
        $this->addSql('DROP INDEX UNIQ_A45BDDC153C674EE ON application');
        $this->addSql('DROP INDEX IDX_A45BDDC1A76ED395 ON application');
        $this->addSql('DROP INDEX IDX_A45BDDC1979B1AD6 ON application');
        $this->addSql('DROP INDEX IDX_A45BDDC19D894366 ON application');
        $this->addSql('ALTER TABLE application DROP user_id, DROP company_id, DROP offer_id, DROP application_method_id');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E979B1AD6');
        $this->addSql('DROP INDEX IDX_29D6873E979B1AD6 ON offer');
        $this->addSql('ALTER TABLE offer DROP company_id');
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F40A76ED395');
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F403E030ACD');
        $this->addSql('DROP INDEX IDX_40374F40A76ED395 ON reminder');
        $this->addSql('DROP INDEX IDX_40374F403E030ACD ON reminder');
        $this->addSql('ALTER TABLE reminder DROP user_id, DROP application_id');
    }
}
