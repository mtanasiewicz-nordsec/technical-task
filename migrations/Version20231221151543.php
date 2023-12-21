<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231221151543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE resolved_addresses (id BIGINT AUTO_INCREMENT NOT NULL, service_provider VARCHAR(60) NOT NULL, country_code VARCHAR(3) NOT NULL, city VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, postcode VARCHAR(100) NOT NULL, lat NUMERIC(11, 8) NOT NULL, lng NUMERIC(11, 8) NOT NULL, created_at DATETIME NOT NULL, INDEX resolved_addresses_search_idx (country_code, city, street, postcode), INDEX resolved_addresses_created_at_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE resolved_addresses');
    }
}
