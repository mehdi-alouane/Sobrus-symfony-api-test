<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021223714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_article (id SERIAL NOT NULL, author_id INT NOT NULL, title VARCHAR(100) NOT NULL, publication_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, content TEXT NOT NULL, keywords JSON NOT NULL, status VARCHAR(20) NOT NULL, slug VARCHAR(255) NOT NULL, cover_picture_ref VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EECCB3E5989D9B62 ON blog_article (slug)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE blog_article');
        $this->addSql('DROP TABLE "user"');
    }
}
