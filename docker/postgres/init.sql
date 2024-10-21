CREATE DATABASE symfony;

\c symfony;

CREATE TABLE BlogArticle (
    id SERIAL PRIMARY KEY,
    author_id INT,
    title VARCHAR(100),
    publication_date TIMESTAMP,
    creation_date TIMESTAMP,
    content TEXT,
    keywords JSON,
    status VARCHAR(20) CHECK (status IN ('draft', 'published', 'deleted')),
    slug VARCHAR(255),
    cover_picture_ref VARCHAR(255)
);