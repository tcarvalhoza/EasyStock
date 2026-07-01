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

## License

MIT
