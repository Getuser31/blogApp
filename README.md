# BlogApp — Laravel + GraphQL + MySQL

A blog API built with **Laravel 12**, **Lighthouse GraphQL**, **MySQL**, and **Pest PHP** testing. The project uses Docker for containerization and is fully GraphQL-driven with Sanctum-based authentication.

---

## 🚀 Quick Start with Docker

### Prerequisites
- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/install/)
- PHP 8.2+ (for local development)
- Composer 2.x (for local development)

### Setup

```bash
# 1. Clone the repository
git clone https://github.com/Getuser31/blogApp.git
cd blogApp

# 2. Copy environment file
cp .env.example .env

# 3. Start Docker services
docker compose up -d

# 4. Install PHP dependencies
docker compose exec app composer install

# 5. Generate application key
docker compose exec app php artisan key:generate

# 6. Run database migrations
docker compose exec app php artisan migrate

# 7. (Optional) Seed the database
docker compose exec app php artisan db:seed
```

The application will be available at:
- **API:** `http://localhost:8000/graphql`
- **Nginx:** `http://localhost:80`

---

## 🐳 Docker Services

| Service   | Image               | Port(s)        | Description                |
|-----------|---------------------|----------------|----------------------------|
| `app`     | PHP 8.3 FPM         | `9000`         | PHP-FPM application server |
| `nginx`   | nginx:latest        | `80`, `8000`   | Web server / reverse proxy |
| `mysql`   | mysql:latest        | `3306`         | MySQL database             |

### Docker Compose Details

- **app** — Runs PHP-FPM and serves the Laravel application. The `Dockerfile` installs `pdo_mysql`, Composer, and necessary system dependencies (libssl, libzip, git).
- **nginx** — Proxies requests to the PHP-FPM container. Serves the Laravel `public/` directory. Configured via `nginx.conf/default.conf`.
- **mysql** — Persisted with a named volume (`mysql_data: /var/lib/mysql`). Default credentials: `laravel` / `laravel`.

### Useful Docker Commands

```bash
# View logs
docker compose logs -f app

# Run artisan command
docker compose exec app php artisan cache:clear

# Run tests inside the container
docker compose exec app php artisan test

# Access MySQL
docker compose exec mysql mysql -u laravel -p laravel
```

---

## 🧪 Testing

The project uses **Pest PHP** for testing. Tests are organized under `tests/Feature/GraphQL/`.

```bash
# Run all tests
php artisan test

# Run only GraphQL tests
php artisan test --filter="Tests\\Feature\\GraphQL"

# Run a specific test file
php artisan test --filter="AdminMutationTest"

# Run inside Docker
docker compose exec app php artisan test
```

### Test Suites (41 passing tests)

| File | Description |
|------|-------------|
| `AdminMutationTest` | Admin-only mutations: create/delete category, update role/status, toggle publish, delete image |
| `ArticleMutationTest` | Article CRUD, comments, favorites, last-read, authorization checks |
| `AuthMutationTest` | Registration, login, invalid credentials, disabled account |
| `AuthenticatedQueryTest` | Me query, user articles/favorites, roles, `getUserData` authorization |
| `QueryTest` | Public queries: published articles, categories, search, user by name |

---

## 🗄️ Database Schema

### Migrations

| Migration | Description |
|-----------|-------------|
| `create_users_table` | Core user accounts |
| `create_cache_table` | Laravel cache |
| `create_jobs_table` | Queued jobs |
| `create_personal_access_tokens_table` | Sanctum API tokens |
| `create_articles_table` | Blog articles (with `author_id`, `published`, timestamps) |
| `add_role_to_users_table` / `migrate_users_role_to_role_id` | Role system |
| `create_role_table` | Roles (Admin, User, etc.) |
| `create_categories_table` | Article categories |
| `create_article_category_table` | Many-to-many pivot (articles ↔ categories) |
| `create_images_table` | Article images |
| `create_comments_table` | Article comments |
| `add_publised_statut_on_articles_table` | Publish toggle |
| `add_favorite_articles_users_table` | Favorites pivot |
| `add_last_read_articles_users_table` | Last-read pivot |
| `add_is_enabled_on_user_table` | User enable/disable |
| `add_uniqness_on_column_name_on_user_table` | Unique `name` constraint |

### Models

| Model | Relationships |
|-------|---------------|
| `User` | Has many HomePage, Comments, FavoriteArticles, LastReadArticles; Belongs to Role |
| `Article` | Belongs to User (author); Belongs to many Categories, Users (favorites, last-read); Has many Images, Comments |
| `Category` | Belongs to many HomePage |
| `Images` | Belongs to Article |
| `Comments` | Belongs to User & Article |
| `Roles` | Has many Users |

---

## ⚡ GraphQL API

The schema is defined in `graphql/schema.graphql` using [Lighthouse PHP](https://lighthouse-php.com/). Authentication is done via **Laravel Sanctum**.

### Queries

| Query | Auth | Description |
|-------|------|-------------|
| `publishedArticles` | ❌ | Paginated published articles (filterable by category) |
| `articlesByUser` | ❌ | Paginated articles by a specific user |
| `article(id)` | ❌ | Single article by ID |
| `getCategories` | ❌ | All categories |
| `searchArticles(search)` | ❌ | Search articles by title/content |
| `me` | ✅ | Current authenticated user |
| `user(id)` | ✅ | Find user by ID |
| `userByName(name)` | ✅ | Find user by name |
| `users` | ✅ | All users |
| `getUserData(id)` | ✅ | User data (own or admin only — custom resolver) |
| `userArticles` | ✅ | Current user's articles |
| `getFavoriteArticles` | ✅ | Current user's favorites |
| `getRoles` | ✅ + Admin | All roles |

### Mutations

| Mutation | Auth | Admin | Description |
|----------|------|-------|-------------|
| `login(email, password)` | ❌ | ❌ | Get auth token (rate-limited) |
| `addUser(username, password, email)` | ❌ | ❌ | Register a new user (rate-limited) |
| `createArticle(title, content, ...)` | ✅ | ✅ | Create article with categories/images |
| `editArticle(id, title, content, ...)` | ✅ | ✅ | Edit an article |
| `addComment(articleId, content)` | ✅ | ❌ | Add comment to article |
| `deleteImage(id)` | ✅ | ❌ | Delete an image |
| `addFavoriteArticle(articleId)` | ✅ | ❌ | Toggle favorite |
| `addLastReadArticle(articleId)` | ✅ | ❌ | Track last read |
| `updatePassword(...)` | ✅ | ❌ | Change password |
| `updateEmail(email)` | ✅ | ❌ | Change email |
| `updateUserStatus(userId)` | ✅ | ✅ | Enable/disable user |
| `togglePublishStatus(articleId)` | ✅ | ❌ | Publish/unpublish article |
| `updateRole(userId, roleId)` | ✅ | ✅ | Change user role |
| `addCategory(name)` | ✅ | ✅ | Create category |
| `deleteCategory(id)` | ✅ | ✅ | Delete category |

### Authentication

All protected queries/mutations use the `@guard(with: ["sanctum"])` directive. To authenticate:

```graphql
mutation {
    login(email: "user@example.com", password: "password") {
        token
        user { id name email }
    }
}
```

Then send the token in the `Authorization: Bearer <token>` header.

Admin-only operations additionally use the `@admin` directive, which checks that the authenticated user has `role_id = 1`.

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 12 (PHP 8.3 FPM) |
| **GraphQL** | Lighthouse PHP |
| **API Auth** | Laravel Sanctum (bearer tokens) |
| **Database** | MySQL 8 |
| **Web Server** | Nginx |
| **Testing** | Pest PHP |
| **Dev Tools** | Laravel Telescope, Laravel Sail, Laravel IDE Helper |
| **Containerization** | Docker & Docker Compose |
| **Storage** | AWS S3 (via `league/flysystem-aws-s3-v3`) |

---

## 📁 Project Structure

```
blogApp/
├── app/
│   ├── GraphQL/
│   │   ├── Directives/        # Custom directives (AdminDirective)
│   │   ├── Mutations/         # GraphQL mutation resolvers
│   │   ├── Queries/           # Custom query resolvers (GetUserData)
│   │   └── Traits/            # Shared traits (ValidatesArticleCreation)
│   ├── Http/
│   │   └── Controllers/Api/   # REST API controllers (ArticleController)
│   └── Models/                # Eloquent models
├── graphql/
│   └── schema.graphql         # GraphQL schema
├── database/
│   ├── factories/             # Model factories (Pest)
│   └── migrations/            # Database migrations (18 files)
├── nginx.conf/
│   └── default.conf           # Nginx configuration
├── tests/
│   └── Feature/GraphQL/       # Pest test files (41 passing tests)
├── docker-compose.yml         # Docker services definition
├── Dockerfile                 # PHP-FPM image build
└── README.md                  # This file
```

---

## 🔧 Development

### Clear GraphQL Schema Cache

Whenever you change `graphql/schema.graphql`, clear the Lighthouse cache:

```bash
php artisan lighthouse:clear-cache
```

### Local Development (without Docker)

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

### Regenerate IDE Helper

```bash
php artisan ide-helper:generate
php artisan ide-helper:models --write
```

---

## 📄 License

This project is open-sourced under the MIT license.
