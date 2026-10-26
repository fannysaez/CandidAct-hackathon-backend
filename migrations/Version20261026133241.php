<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261026133241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, apply_date DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, user_id INT NOT NULL, company_id INT NOT NULL, offer_id INT DEFAULT NULL, application_method_id INT DEFAULT NULL, application_status_id INT NOT NULL, UNIQUE INDEX UNIQ_A45BDDC153C674EE (offer_id), INDEX IDX_A45BDDC1A76ED395 (user_id), INDEX IDX_A45BDDC1979B1AD6 (company_id), INDEX IDX_A45BDDC19D894366 (application_method_id), INDEX IDX_A45BDDC1EFF67ABC (application_status_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE application_method (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, job_title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, contract_type VARCHAR(255) DEFAULT NULL, publication_date DATE NOT NULL, expiration_date DATE DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_29D6873E979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reminder (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, date DATETIME NOT NULL, user_id INT NOT NULL, application_id INT NOT NULL, INDEX IDX_40374F40A76ED395 (user_id), INDEX IDX_40374F403E030ACD (application_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, status_application_id INT NOT NULL, INDEX IDX_7B00651CBE4BA557 (status_application_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE status_application (id INT AUTO_INCREMENT NOT NULL, update_date DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC153C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC19D894366 FOREIGN KEY (application_method_id) REFERENCES application_method (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1EFF67ABC FOREIGN KEY (application_status_id) REFERENCES status_application (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F403E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651CBE4BA557 FOREIGN KEY (status_application_id) REFERENCES status_application (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1A76ED395');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1979B1AD6');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC153C674EE');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC19D894366');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1EFF67ABC');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E979B1AD6');
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F40A76ED395');
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F403E030ACD');
        $this->addSql('ALTER TABLE status DROP FOREIGN KEY FK_7B00651CBE4BA557');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE application_method');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE reminder');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE status_application');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
