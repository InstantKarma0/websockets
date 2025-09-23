<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923192554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversations (id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversations_user (conversations_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B40E4033FE142757 (conversations_id), INDEX IDX_B40E4033A76ED395 (user_id), PRIMARY KEY(conversations_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversations_user ADD CONSTRAINT FK_B40E4033FE142757 FOREIGN KEY (conversations_id) REFERENCES conversations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversations_user ADD CONSTRAINT FK_B40E4033A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversations_user DROP FOREIGN KEY FK_B40E4033FE142757');
        $this->addSql('ALTER TABLE conversations_user DROP FOREIGN KEY FK_B40E4033A76ED395');
        $this->addSql('DROP TABLE conversations');
        $this->addSql('DROP TABLE conversations_user');
    }
}
