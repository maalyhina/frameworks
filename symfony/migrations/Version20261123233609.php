<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261123233609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE hall (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, capacity INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE movie (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, duration INT NOT NULL, price NUMERIC(8, 2) NOT NULL, genre_id INT NOT NULL, INDEX IDX_1D5EF26F4296D31F (genre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE screening (id INT AUTO_INCREMENT NOT NULL, starts_at DATETIME NOT NULL, movie_id INT NOT NULL, hall_id INT NOT NULL, INDEX IDX_B708297D8F93B6FC (movie_id), INDEX IDX_B708297D52AFCFD6 (hall_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ticket_order (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, created_at DATETIME NOT NULL, customer_id INT NOT NULL, screening_id INT NOT NULL, INDEX IDX_DD19F0139395C3F3 (customer_id), INDEX IDX_DD19F01370F5295D (screening_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26F4296D31F FOREIGN KEY (genre_id) REFERENCES genre (id)');
        $this->addSql('ALTER TABLE screening ADD CONSTRAINT FK_B708297D8F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE screening ADD CONSTRAINT FK_B708297D52AFCFD6 FOREIGN KEY (hall_id) REFERENCES hall (id)');
        $this->addSql('ALTER TABLE ticket_order ADD CONSTRAINT FK_DD19F0139395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE ticket_order ADD CONSTRAINT FK_DD19F01370F5295D FOREIGN KEY (screening_id) REFERENCES screening (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie DROP FOREIGN KEY FK_1D5EF26F4296D31F');
        $this->addSql('ALTER TABLE screening DROP FOREIGN KEY FK_B708297D8F93B6FC');
        $this->addSql('ALTER TABLE screening DROP FOREIGN KEY FK_B708297D52AFCFD6');
        $this->addSql('ALTER TABLE ticket_order DROP FOREIGN KEY FK_DD19F0139395C3F3');
        $this->addSql('ALTER TABLE ticket_order DROP FOREIGN KEY FK_DD19F01370F5295D');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE hall');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE screening');
        $this->addSql('DROP TABLE ticket_order');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
