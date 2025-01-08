<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108091809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F61220EA6');
        $this->addSql('DROP INDEX IDX_C4E0A61F61220EA6 ON team');
        $this->addSql('ALTER TABLE team ADD score DOUBLE PRECISION NOT NULL, ADD nb_encounter INT NOT NULL, ADD nb_goals INT NOT NULL, ADD inscription_date DATE NOT NULL, ADD nb_win INT NOT NULL, CHANGE creator_id total_points INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD my_team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A3957482 FOREIGN KEY (my_team_id) REFERENCES team (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649A3957482 ON user (my_team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A3957482');
        $this->addSql('DROP INDEX UNIQ_8D93D649A3957482 ON user');
        $this->addSql('ALTER TABLE user DROP my_team_id');
        $this->addSql('ALTER TABLE team ADD creator_id INT NOT NULL, DROP total_points, DROP score, DROP nb_encounter, DROP nb_goals, DROP inscription_date, DROP nb_win');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_C4E0A61F61220EA6 ON team (creator_id)');
    }
}
