<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210718085942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE driver (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, market_price INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hired_driver (id INT AUTO_INCREMENT NOT NULL, driver_slot_id INT NOT NULL, driver_id INT NOT NULL, UNIQUE INDEX UNIQ_BBC20F0F9C1A75FC (driver_slot_id), INDEX IDX_BBC20F0FC3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hired_mechanic (id INT AUTO_INCREMENT NOT NULL, mechanic_slot_id INT NOT NULL, mechanic_id INT NOT NULL, UNIQUE INDEX UNIQ_6F5936C2B883FC28 (mechanic_slot_id), INDEX IDX_6F5936C29A67DB00 (mechanic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mechanic (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, market_price INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hired_driver ADD CONSTRAINT FK_BBC20F0F9C1A75FC FOREIGN KEY (driver_slot_id) REFERENCES driver_slot (id)');
        $this->addSql('ALTER TABLE hired_driver ADD CONSTRAINT FK_BBC20F0FC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id)');
        $this->addSql('ALTER TABLE hired_mechanic ADD CONSTRAINT FK_6F5936C2B883FC28 FOREIGN KEY (mechanic_slot_id) REFERENCES mechanic_slot (id)');
        $this->addSql('ALTER TABLE hired_mechanic ADD CONSTRAINT FK_6F5936C29A67DB00 FOREIGN KEY (mechanic_id) REFERENCES mechanic (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hired_driver DROP FOREIGN KEY FK_BBC20F0FC3423909');
        $this->addSql('ALTER TABLE hired_mechanic DROP FOREIGN KEY FK_6F5936C29A67DB00');
        $this->addSql('DROP TABLE driver');
        $this->addSql('DROP TABLE hired_driver');
        $this->addSql('DROP TABLE hired_mechanic');
        $this->addSql('DROP TABLE mechanic');
    }
}
