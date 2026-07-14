# EasyStock

Product management system with inventory control and fiscal coupon generation.

## Features

- User registration with role-based access control
- Product management with stock control
- Sales processing with fiscal coupon generation
- Unit testing with PHPUnit
- PSR-12 compliant code
- SOLID principles architecture

## Requirements

- Docker
- Docker Compose
- PHP 8.4+

## Installation

1. Clone the repository
2. Run `docker-compose up -d --build`
3. Install dependencies: `docker-compose exec app composer install`
4. Copy environment file: `docker-compose exec app cp .env.example .env`
5. Generate application key: `docker-compose exec app php artisan key:generate`
6. Run migrations: `docker-compose exec app php artisan migrate`
7. Run tests: `docker-compose exec app php artisan test`

## Seeding

### Roles (required)

The `RoleSeeder` creates the three access profiles (`admin`, `manager`, `cashier`). It must be run after migrations:

```bash
docker-compose exec app php artisan db:seed
```

Or to run only the `RoleSeeder`:

```bash
docker-compose exec app php artisan db:seed --class=RoleSeeder
```

Roles created:

| Role | Description |
|---|---|
| `admin` | Full access |
| `manager` | Product and sales access |
| `cashier` | Sales access only |

### Test Data (Factories)

The project has factories for `User` and `Product`. To generate data via Tinker:

```bash
docker-compose exec app php artisan tinker
```

```php
// Create 10 products
App\Models\Product::factory(10)->create();

// Create a user with password "password"
App\Models\User::factory()->create(['email' => 'admin@example.com']);
```

> **Note:** After creating a user via factory, assign a role via the API (`POST /api/users/{id}/role`) or directly in the database.

---

## Architecture

The project follows SOLID principles:

- **Single Responsibility**: Controllers are thin, business logic is in Services
- **Open/Closed**: Use interfaces and dependency injection
- **Liskov Substitution**: Proper inheritance and interface implementation
- **Interface Segregation**: Specific interfaces for different contracts
- **Dependency Inversion**: Depend on abstractions, not concretions

## Services

- `UserService`: User management and authentication
- `ProductService`: Product CRUD and stock management
- `SaleService`: Sales processing and fiscal coupon generation

## Testing

Run all tests:
```bash
docker-compose exec app php artisan test
```

Run specific test suite:
```bash
docker-compose exec app php artisan test --testsuite=Unit
docker-compose exec app php artisan test --testsuite=Feature
```

## Code Style

Check PSR-12 compliance:
```bash
docker-compose exec app ./vendor/bin/phpcs --standard=PSR12
```

## Environment Variables

Copy `.env.example` to `.env` and adjust the values below. Only the **required** variables need to be changed for the app to work.

### Required

| Variable | Description | Default |
|---|---|---|
| `APP_KEY` | Laravel application key — generate with `php artisan key:generate` | *(empty)* |
| `DB_HOST` | MySQL host | `db` *(Docker service name)* |
| `DB_PORT` | MySQL port | `3306` |
| `DB_DATABASE` | Database name | `easystock` |
| `DB_USERNAME` | Database user | `easystock` |
| `DB_PASSWORD` | Database password | `secret` |

### Application

| Variable | Description | Default |
|---|---|---|
| `APP_NAME` | Application name | `EasyStock` |
| `APP_ENV` | Environment (`local`, `production`) | `local` |
| `APP_DEBUG` | Enable debug mode | `true` |
| `APP_URL` | Base URL | `http://localhost:8000` |
| `APP_TIMEZONE` | Timezone | `UTC` |

### Logging

| Variable | Description | Default |
|---|---|---|
| `LOG_CHANNEL` | Log channel (`stack`, `single`, `daily`) | `stack` |
| `LOG_LEVEL` | Log level (`debug`, `info`, `warning`, `error`) | `debug` |

### Cache & Queue

| Variable | Description | Default |
|---|---|---|
| `CACHE_STORE` | Cache driver (`file`, `redis`, `database`) | `file` |
| `QUEUE_CONNECTION` | Queue driver (`sync`, `database`, `redis`) | `database` |

> **Note:** Redis, Mail, AWS, and Pusher variables are optional and only required if those integrations are used.

---

## API Usage Examples

All authenticated requests require the `Authorization: Bearer {token}` header.
Base URL: `http://localhost:8000/api`

### Auth

**Register**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com", "password": "password123", "password_confirmation": "password123"}'
```

**Login**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "password123"}'
```
Response:
```json
{"token": "1|abc123..."}
```

**Logout**
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer {token}"
```

**Get authenticated user**
```bash
curl http://localhost:8000/api/user/me \
  -H "Authorization: Bearer {token}"
```

---

### Users

**Assign role to user** *(admin only)*
```bash
curl -X POST http://localhost:8000/api/users/2/role \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"role": "manager"}'
```
Available roles: `admin`, `manager`, `cashier`

---

### Products

*(requires `admin` or `manager` role)*

**List products**
```bash
curl http://localhost:8000/api/products \
  -H "Authorization: Bearer {token}"
```
Optional query params: `name`, `is_active`, `per_page`
```bash
curl "http://localhost:8000/api/products?name=notebook&is_active=1&per_page=10" \
  -H "Authorization: Bearer {token}"
```

**Create product**
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"name": "Notebook", "sku": "NB-001", "description": "15 inch laptop", "price": 2999.99, "stock_quantity": 50, "is_active": true}'
```

**Get product**
```bash
curl http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}"
```

**Update product**
```bash
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"price": 2799.99, "is_active": false}'
```

**Delete product**
```bash
curl -X DELETE http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}"
```

**Update stock** *(positive = entry, negative = exit)*
```bash
curl -X POST http://localhost:8000/api/products/1/stock \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"quantity": 20}'
```

---

### Sales

*(requires `admin`, `manager` or `cashier` role)*

**Create sale**
```bash
curl -X POST http://localhost:8000/api/sales \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"items": [{"product_id": 1, "quantity": 2}, {"product_id": 3, "quantity": 1}]}'
```
Response:
```json
{
  "id": 1,
  "status": "pending",
  "total": "149.97",
  "items": [...]
}
```

**Get sale**
```bash
curl http://localhost:8000/api/sales/1 \
  -H "Authorization: Bearer {token}"
```

**Complete sale**
```bash
curl -X POST http://localhost:8000/api/sales/1/complete \
  -H "Authorization: Bearer {token}"
```

**Cancel sale** *(restores stock)*
```bash
curl -X POST http://localhost:8000/api/sales/1/cancel \
  -H "Authorization: Bearer {token}"
```

**Generate fiscal coupon**
```bash
curl http://localhost:8000/api/sales/1/coupon \
  -H "Authorization: Bearer {token}"
```
Response:
```json
{"coupon": "SALE #1\nDate: 2024-01-15 10:30:00\n..."}
```

---

## Deploy on Fly.io

The application is packaged in a single container with PHP-FPM + Nginx + Supervisor, ready for Fly.io.

A `fly.toml` is already provided. The default app name is `easystock-api`; change it if the name is already taken.

### Install the Fly.io CLI

```bash
curl -L https://fly.io/install.sh | sh
```

Authenticate:

```bash
flyctl auth login
```

### Create the app and deploy

```bash
flyctl apps create easystock-api
flyctl secrets set APP_KEY='<YOUR_APP_KEY>' \
  APP_ENV=production \
  APP_DEBUG=false \
  APP_URL='https://easystock-api.fly.dev' \
  DB_CONNECTION=sqlite \
  DB_DATABASE=/var/www/storage/database.sqlite
flyctl deploy
```

> **Tip:** generate a secure `APP_KEY` with `php artisan key:generate --show` and set it as a secret. Without it, the container will generate a temporary key on each startup, invalidating sessions and cached data.

The container will automatically run `php artisan migrate --force` on startup and optimize Laravel caches when `APP_ENV=production`.

Access the deployed app at `https://easystock-api.fly.dev`.

## Deploy on Koyeb

The application is packaged in a single container with PHP-FPM + Nginx + Supervisor, ready for Koyeb.

### Build and run locally (Docker Compose)

```bash
docker-compose up -d --build
```

The API will be available at `http://localhost:8000`.

### Required environment variables for production

Configure these in the Koyeb dashboard (or via CLI) when creating the service:

| Variable | Description |
|---|---|
| `APP_KEY` | Laravel application key — generate with `php artisan key:generate --show` locally and paste the value |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Public URL of the Koyeb service, e.g. `https://easystock-<org>.koyeb.app` |
| `DB_CONNECTION` | `mysql` or `pgsql` |
| `DB_HOST` | Database host |
| `DB_PORT` | Database port |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password |

> **Tip:** generate a secure `APP_KEY` with `php artisan key:generate --show` and set it as an environment variable in Koyeb. Without it, the container will generate a temporary key on each startup, invalidating sessions and cached data.

### Deploy from GitHub (Dockerfile builder)

1. Push the repository to GitHub.
2. In the [Koyeb control panel](https://app.koyeb.com), click **Create Web Service**.
3. Select **GitHub** and choose the `EasyStock` repository.
4. Choose **Dockerfile** as the builder.
5. Add the environment variables listed above.
6. Expose port `8080` and route `/` to it.
7. Deploy.

The container will automatically run `php artisan migrate --force` on startup (if `DB_HOST` is set) and optimize Laravel caches when `APP_ENV=production`.

### Deploy using the Koyeb CLI

```bash
# Install the Koyeb CLI and authenticate first:
# https://www.koyeb.com/docs/cli/installation

koyeb app init easystock \
  --git github.com/<YOUR_GITHUB_USERNAME>/EasyStock \
  --git-branch main \
  --git-builder docker \
  --ports 8080:http \
  --routes '/:8080' \
  --env APP_KEY='<YOUR_APP_KEY>' \
  --env APP_ENV=production \
  --env APP_DEBUG=false \
  --env APP_URL='https://easystock-<ORG>.koyeb.app' \
  --env DB_CONNECTION=mysql \
  --env DB_HOST='<DB_HOST>' \
  --env DB_PORT=3306 \
  --env DB_DATABASE=easystock \
  --env DB_USERNAME='<DB_USER>' \
  --env DB_PASSWORD='<DB_PASSWORD>'
```

## License

MIT
