<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604023116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE absence (id INT AUTO_INCREMENT NOT NULL, enseignant_id INT DEFAULT NULL, date DATE NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, INDEX IDX_765AE0C9E455FCC0 (enseignant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, enseignant_id INT DEFAULT NULL, département_id INT DEFAULT NULL, niveau_id INT DEFAULT NULL, nom_cours VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_FDCA8C9CE455FCC0 (enseignant_id), INDEX IDX_FDCA8C9CAE548A03 (département_id), INDEX IDX_FDCA8C9CB3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE disponibilité (id INT AUTO_INCREMENT NOT NULL, enseignant_id INT DEFAULT NULL, salle_id INT DEFAULT NULL, date DATE NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, INDEX IDX_2EA86839E455FCC0 (enseignant_id), INDEX IDX_2EA86839DC304035 (salle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE département (id INT AUTO_INCREMENT NOT NULL, nom_département VARCHAR(255) NOT NULL, code VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_40B53A73CD73572D (nom_département), UNIQUE INDEX UNIQ_40B53A7377153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE emploi_du_temps (id INT AUTO_INCREMENT NOT NULL, cours_id INT DEFAULT NULL, salle_id INT DEFAULT NULL, département_id INT DEFAULT NULL, niveau_id INT DEFAULT NULL, date DATE NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, duree VARCHAR(8) NOT NULL, INDEX IDX_F86B32C17ECF78B0 (cours_id), INDEX IDX_F86B32C1DC304035 (salle_id), INDEX IDX_F86B32C1AE548A03 (département_id), INDEX IDX_F86B32C1B3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE niveau (id INT AUTO_INCREMENT NOT NULL, nom_niveau VARCHAR(50) NOT NULL, code VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_4BDFF36BCB106C3C (nom_niveau), UNIQUE INDEX UNIQ_4BDFF36B77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, nom_salle VARCHAR(255) NOT NULL, capacite INT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE suit (utilisateur_id INT NOT NULL, cours_id INT NOT NULL, INDEX IDX_E9A31F1EFB88E14F (utilisateur_id), INDEX IDX_E9A31F1E7ECF78B0 (cours_id), PRIMARY KEY(utilisateur_id, cours_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, département_id INT DEFAULT NULL, niveau_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), INDEX IDX_1D1C63B3AE548A03 (département_id), INDEX IDX_1D1C63B3B3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE absence ADD CONSTRAINT FK_765AE0C9E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CE455FCC0 FOREIGN KEY (enseignant_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CAE548A03 FOREIGN KEY (département_id) REFERENCES département (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE disponibilité ADD CONSTRAINT FK_2EA86839E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE disponibilité ADD CONSTRAINT FK_2EA86839DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C17ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C1DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C1AE548A03 FOREIGN KEY (département_id) REFERENCES département (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C1B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suit ADD CONSTRAINT FK_E9A31F1EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suit ADD CONSTRAINT FK_E9A31F1E7ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3AE548A03 FOREIGN KEY (département_id) REFERENCES département (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE absence DROP FOREIGN KEY FK_765AE0C9E455FCC0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CE455FCC0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CAE548A03
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CB3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE disponibilité DROP FOREIGN KEY FK_2EA86839E455FCC0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE disponibilité DROP FOREIGN KEY FK_2EA86839DC304035
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C17ECF78B0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C1DC304035
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C1AE548A03
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C1B3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suit DROP FOREIGN KEY FK_E9A31F1EFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suit DROP FOREIGN KEY FK_E9A31F1E7ECF78B0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3AE548A03
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3B3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE absence
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cours
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE disponibilité
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE département
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE emploi_du_temps
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE niveau
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE salle
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE suit
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
