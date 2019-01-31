<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190111161507 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE battle_battle (id INT AUTO_INCREMENT NOT NULL, programmer_id INT NOT NULL, project_id INT NOT NULL, fought_at DATETIME NOT NULL, did_programmer_win TINYINT(1) NOT NULL, notes LONGTEXT NOT NULL, INDEX IDX_36EFFEC5181DAE45 (programmer_id), INDEX IDX_36EFFEC5166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE programmer (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nickname VARCHAR(100) NOT NULL, avatar_number INT NOT NULL, tag_line VARCHAR(255) DEFAULT NULL, power_level INT NOT NULL, UNIQUE INDEX UNIQ_4136CCA9A188FE64 (nickname), INDEX IDX_4136CCA9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, difficulty_level INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE battle_battle ADD CONSTRAINT FK_36EFFEC5181DAE45 FOREIGN KEY (programmer_id) REFERENCES programmer (id)');
        $this->addSql('ALTER TABLE battle_battle ADD CONSTRAINT FK_36EFFEC5166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE programmer ADD CONSTRAINT FK_4136CCA9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE battle_battle DROP FOREIGN KEY FK_36EFFEC5181DAE45');
        $this->addSql('ALTER TABLE battle_battle DROP FOREIGN KEY FK_36EFFEC5166D1F9C');
        $this->addSql('ALTER TABLE programmer DROP FOREIGN KEY FK_4136CCA9A76ED395');
        $this->addSql('DROP TABLE battle_battle');
        $this->addSql('DROP TABLE programmer');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE user');
    }
}
