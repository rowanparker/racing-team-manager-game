<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210710074331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE car (id INT AUTO_INCREMENT NOT NULL, make VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, year INT NOT NULL, market_price INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE driver_slot (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, INDEX IDX_4BA4C6F2296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE garage_slot (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, INDEX IDX_CF785D8C296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mechanic_slot (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, INDEX IDX_D462A7CA296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE owned_car (id INT AUTO_INCREMENT NOT NULL, garage_slot_id INT NOT NULL, car_id INT NOT NULL, UNIQUE INDEX UNIQ_37569AF5DFA5FD0C (garage_slot_id), INDEX IDX_37569AF5C3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, balance_credits INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE driver_slot ADD CONSTRAINT FK_4BA4C6F2296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE garage_slot ADD CONSTRAINT FK_CF785D8C296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE mechanic_slot ADD CONSTRAINT FK_D462A7CA296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE owned_car ADD CONSTRAINT FK_37569AF5DFA5FD0C FOREIGN KEY (garage_slot_id) REFERENCES garage_slot (id)');
        $this->addSql('ALTER TABLE owned_car ADD CONSTRAINT FK_37569AF5C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE owned_car DROP FOREIGN KEY FK_37569AF5C3C6F69F');
        $this->addSql('ALTER TABLE owned_car DROP FOREIGN KEY FK_37569AF5DFA5FD0C');
        $this->addSql('ALTER TABLE driver_slot DROP FOREIGN KEY FK_4BA4C6F2296CD8AE');
        $this->addSql('ALTER TABLE garage_slot DROP FOREIGN KEY FK_CF785D8C296CD8AE');
        $this->addSql('ALTER TABLE mechanic_slot DROP FOREIGN KEY FK_D462A7CA296CD8AE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649296CD8AE');
        $this->addSql('DROP TABLE car');
        $this->addSql('DROP TABLE driver_slot');
        $this->addSql('DROP TABLE garage_slot');
        $this->addSql('DROP TABLE mechanic_slot');
        $this->addSql('DROP TABLE owned_car');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE user');
    }
}
