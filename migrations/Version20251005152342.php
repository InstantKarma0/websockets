<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005152342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation_message (id INT AUTO_INCREMENT NOT NULL, ref_conversation_id INT NOT NULL, ref_user_id INT NOT NULL, content VARCHAR(255) NOT NULL, INDEX IDX_2DEB3E75995E8836 (ref_conversation_id), INDEX IDX_2DEB3E75637A8045 (ref_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E75995E8836 FOREIGN KEY (ref_conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E75637A8045 FOREIGN KEY (ref_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE conversation_user DROP FOREIGN KEY FK_B40E4033FE142757');
        $this->addSql('DROP INDEX IDX_B40E4033FE142757 ON conversation_user');
        $this->addSql('DROP INDEX `primary` ON conversation_user');
        $this->addSql('ALTER TABLE conversation_user CHANGE conversations_id conversation_id INT NOT NULL');
        $this->addSql('ALTER TABLE conversation_user ADD CONSTRAINT FK_5AECB5559AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5AECB5559AC0396 ON conversation_user (conversation_id)');
        $this->addSql('ALTER TABLE conversation_user ADD PRIMARY KEY (conversation_id, user_id)');
        $this->addSql('ALTER TABLE conversation_user RENAME INDEX idx_b40e4033a76ed395 TO IDX_5AECB555A76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E75995E8836');
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E75637A8045');
        $this->addSql('DROP TABLE conversation_message');
        $this->addSql('ALTER TABLE conversation_user DROP FOREIGN KEY FK_5AECB5559AC0396');
        $this->addSql('DROP INDEX IDX_5AECB5559AC0396 ON conversation_user');
        $this->addSql('DROP INDEX `PRIMARY` ON conversation_user');
        $this->addSql('ALTER TABLE conversation_user CHANGE conversation_id conversations_id INT NOT NULL');
        $this->addSql('ALTER TABLE conversation_user ADD CONSTRAINT FK_B40E4033FE142757 FOREIGN KEY (conversations_id) REFERENCES conversation (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B40E4033FE142757 ON conversation_user (conversations_id)');
        $this->addSql('ALTER TABLE conversation_user ADD PRIMARY KEY (conversations_id, user_id)');
        $this->addSql('ALTER TABLE conversation_user RENAME INDEX idx_5aecb555a76ed395 TO IDX_B40E4033A76ED395');
    }
}
