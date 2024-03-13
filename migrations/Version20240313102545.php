<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240313102545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C58AFC4DE');
        $this->addSql('ALTER TABLE team_league DROP FOREIGN KEY FK_48AF84C1296CD8AE');
        $this->addSql('ALTER TABLE team_league DROP FOREIGN KEY FK_48AF84C158AFC4DE');
        $this->addSql('ALTER TABLE user_league DROP FOREIGN KEY FK_5BE6D82558AFC4DE');
        $this->addSql('ALTER TABLE user_league DROP FOREIGN KEY FK_5BE6D825A76ED395');
        $this->addSql('DROP TABLE league');
        $this->addSql('DROP TABLE team_league');
        $this->addSql('DROP TABLE user_league');
        $this->addSql('DROP INDEX IDX_232B318C58AFC4DE ON game');
        $this->addSql('ALTER TABLE game DROP league_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE league (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, team_size INT NOT NULL, round INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE team_league (team_id INT NOT NULL, league_id INT NOT NULL, INDEX IDX_48AF84C1296CD8AE (team_id), INDEX IDX_48AF84C158AFC4DE (league_id), PRIMARY KEY(team_id, league_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_league (user_id INT NOT NULL, league_id INT NOT NULL, INDEX IDX_5BE6D825A76ED395 (user_id), INDEX IDX_5BE6D82558AFC4DE (league_id), PRIMARY KEY(user_id, league_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE team_league ADD CONSTRAINT FK_48AF84C1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_league ADD CONSTRAINT FK_48AF84C158AFC4DE FOREIGN KEY (league_id) REFERENCES league (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_league ADD CONSTRAINT FK_5BE6D82558AFC4DE FOREIGN KEY (league_id) REFERENCES league (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_league ADD CONSTRAINT FK_5BE6D825A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game ADD league_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C58AFC4DE FOREIGN KEY (league_id) REFERENCES league (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_232B318C58AFC4DE ON game (league_id)');
    }
}
