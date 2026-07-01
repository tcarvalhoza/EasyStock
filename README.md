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

## License

MIT
