<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190830143706 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE filter (id INT AUTO_INCREMENT NOT NULL, filterName VARCHAR(255) NOT NULL, numberType VARCHAR(255) DEFAULT NULL, numberValue VARCHAR(255) DEFAULT NULL, customerType VARCHAR(255) DEFAULT NULL, customerValue VARCHAR(255) DEFAULT NULL, dateFrom DATETIME DEFAULT NULL, dateTo DATETIME DEFAULT NULL, channel VARCHAR(255) DEFAULT NULL, totalGreaterThan VARCHAR(255) DEFAULT NULL, totalLessThan VARCHAR(255) DEFAULT NULL, totalCurrency VARCHAR(255) DEFAULT NULL, orderState VARCHAR(255) DEFAULT NULL, paymentState VARCHAR(255) DEFAULT NULL, shippingState VARCHAR(255) DEFAULT NULL, shippingCountry VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_7FC45F1D54AE4910 (filterName), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE batch (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE batch_order (batch_id INT NOT NULL, order_id INT NOT NULL, INDEX IDX_D8BB18FFF39EBE7A (batch_id), INDEX IDX_D8BB18FF8D9F6D38 (order_id), PRIMARY KEY(batch_id, order_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE batch_order ADD CONSTRAINT FK_D8BB18FFF39EBE7A FOREIGN KEY (batch_id) REFERENCES batch (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE batch_order ADD CONSTRAINT FK_D8BB18FF8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE batch_order DROP FOREIGN KEY FK_D8BB18FFF39EBE7A');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE batch');
        $this->addSql('DROP TABLE batch_order');
    }
}
