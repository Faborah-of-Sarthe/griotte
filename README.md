## griotte

### First install

#### Requirements

Griotte requires PHP 8.0 to 8.2

#### Backend

1. `cd api`
1. `composer install`
1. `cp .env.example .env` -> update with your config
1. `php artisan key:generate`
1. `php artisan migrate`
1. `php artisan serve`

#### Frontend

1. `cd frontend`
1. `npm install`
1. `cp .env.example .env` -> update with your config
1. `npm run start`

### Docker

Docker is only use for production right now. Please do not modify docker-compose.yml