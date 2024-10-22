# Blog API

A RESTful API built with Symfony for managing blog articles with features like content moderation, keyword generation, and user authentication.

## Features

- CRUD operations for blog articles
- User registration and authentication
- Automatic keyword generation from content
- Content moderation with banned words filtering
- Slug generation for SEO-friendly URLs
- Cover image upload support
- OpenAPI/Swagger documentation
- Docker support for easy development and deployment

## Requirements

- Docker and Docker Compose
- Git

For local development without Docker:
- PHP 8.0 or higher
- Symfony 6.x
- Doctrine ORM
- PostgreSQL 13

## Installation

### Using Docker (Recommended)

1. Clone the repository:
```bash
git clone https://github.com/mehdi-alouane/Sobrus-symfony-api-test
cd Sobrus-symfony-api-test
```

2. Start the Docker containers:
```bash
docker-compose up -d
```

This will set up:
- PHP service with the application
- Nginx web server (accessible at http://localhost:8080)
- PostgreSQL database

3. Install dependencies inside the PHP container:
```bash
docker-compose exec php composer install
```

4. Run migrations:
```bash
docker-compose exec php bin/console doctrine:migrations:migrate
```

### Local Installation (Alternative)

1. Clone the repository and install dependencies:
```bash
git clone https://github.com/mehdi-alouane/Sobrus-symfony-api-test
cd Sobrus-symfony-api-test
composer install
```

2. Configure your database in `.env`:
```
DATABASE_URL="postgresql://symfony:symfony_pass@127.0.0.1:5432/symfony?serverVersion=13&charset=utf8"
```

3. Create database and run migrations:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Docker Configuration

### Services

#### PHP
- Custom PHP-FPM image built from Dockerfile
- Application code mounted at `/var/www/html`
- Connected to PostgreSQL database

#### Nginx
- Alpine-based Nginx image
- Exposed on port 8080
- Custom configuration in `docker/nginx/default.conf`

#### PostgreSQL
- Version 13
- Credentials:
  - Database: symfony
  - User: symfony
  - Password: symfony_pass
- Data persisted in named volume `db_data`
- Custom initialization script in `docker/postgres/init.sql`
- Exposed on port 5432

### Docker Commands

Start services:
```bash
docker-compose up -d
```

Stop services:
```bash
docker-compose down
```

View logs:
```bash
docker-compose logs -f
```

Execute commands in PHP container:
```bash
docker-compose exec php [command]
```

Rebuild containers:
```bash
docker-compose up -d --build
```

## API Endpoints

### Blog Articles

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/blog-articles` | Get all blog articles |
| POST | `/api/blog-articles` | Create a new blog article |
| GET | `/api/blog-articles/{id}` | Get a specific blog article |
| PATCH | `/api/blog-articles/{id}` | Update a blog article |
| DELETE | `/api/blog-articles/{id}` | Soft delete a blog article |

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register a new user |

## Request Examples

### Create Blog Article
```json
POST /api/blog-articles
{
    "authorId": 1,
    "title": "My First Blog Post",
    "content": "Article content goes here...",
    "status": "draft",
    "publicationDate": "2024-10-22T12:00:00Z"
}
```

### Register User
```json
POST /api/register
{
    "email": "user@example.com",
    "password": "securepassword"
}
```

## Features Details

### Content Moderation
- Automatic filtering of banned words
- Current banned words: ["thor"]
- Returns 400 Bad Request if banned words are detected

### Keyword Generation
- Automatically generates keywords from article content
- Excludes banned words from keyword generation
- Uses frequency analysis to determine important terms

### File Uploads
- Supports cover image uploads for blog articles
- Files are stored in the configured uploads directory
- Generates unique filenames using MD5 hash

## Development

### Adding New Banned Words
Modify the `$bannedWords` array in `BlogArticleController.php`:
```php
private $bannedWords = ['thor', 'new_banned_word'];
```

### Running Tests
With Docker:
```bash
docker-compose exec php bin/phpunit
```

Without Docker:
```bash
php bin/phpunit
```

