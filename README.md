# EasyBites

EasyBites is a local food-delivery project for Sarajevo, Bosnia and Herzegovina. It helps people discover the city's neighbourhood restaurants and, eventually, order from them.

> **Milestone 3 status:** The backend has validated services and FlightPHP CRUD endpoints for all six database entities, an OpenAPI 3 specification with a Swagger UI page, and a Sarajevo weather/dining suggestion feature backed by Open-Meteo. The Discover page consumes the weather endpoint. Other restaurant, menu, basket, order, profile, sign-in, and admin screens remain static demonstrations and do not persist data.

## Project structure

```text
backend/
  config/       Safe config template and ignored local config
  database/     MySQL schema and sample restaurant/menu data
  dao/          PDO data-access objects
  logs/         Ignored runtime info and error logs
  public/       FlightPHP entry point, Apache rewrite rules, and OpenAPI/Swagger documentation
  routes/       Per-entity CRUD routes, error handlers, and HTTP request/response helpers
  services/     Validation, business rules, logging, weather integration
  tests/        Isolated SQLite-backed HTTP integration checks
docs/
  AI_LOG.md     AI-use notes by milestone
  ERD.md        Entity relationships
frontend/
  css/          Responsive application styles
  js/           SPA fragment loader, interactions, and frontend service modules
  tests/        Frontend weather-service checks
  views/        Separately loaded SPA page fragments
  index.html    SPA shell with hash/history navigation
composer.json   FlightPHP and swagger-php dependencies and class autoloading
```

## Entities and relationships

The MySQL schema contains **Users, Restaurants, MenuItems, Orders, OrderItems, and Reviews**. A user can place many orders and write reviews; a restaurant owns menu items and receives orders and reviews; each order contains order items that refer to menu items. `OrderItems` records purchase-time quantity and price. See the [ERD](docs/ERD.md) and [schema export](backend/database/easybites.sql).

Each entity has a DAO inheriting prepared-statement CRUD operations from `BaseDAO`. Its service validates application input, checks related records, and is the only layer that calls DAOs. FlightPHP routes handle JSON and HTTP status codes. Review creation requires a delivered order from that restaurant, rejects HTML markup, and prevents duplicate reviews at the service layer as well as through the database constraint.

## Roles and test accounts

The schema defines **customer** and **admin** roles. M3 customer creation hashes passwords server-side; password hashes are omitted from API responses, and the M3 API does not accept role changes. There are no seeded or working login accounts yet. Authentication, JWTs, and role authorization belong to Milestone 4; the API is currently unauthenticated and is for local development only. **Do not expose it to the internet.**

## External API

The Discover page displays current Sarajevo weather and a practical weather-aware dining tip using the free, no-key [Open-Meteo API](https://open-meteo.com/). The backend endpoint is `GET /api/v1/weather/sarajevo`; it validates the upstream response, has a five-second timeout, logs success/failure, and returns a user-friendly `503` during provider outages. The frontend shows loading and retry/fallback states so restaurant discovery remains usable if weather is unavailable. The UI credits Open-Meteo.

## Run locally

Install PHP 8.2 or newer with PDO MySQL and SQLite extensions, MySQL 8.0.16 or newer (the schema uses enforced `CHECK` constraints) or the XAMPP MariaDB version verified for M2, and Composer.

1. Install the PHP dependency:

   ```sh
   composer install
   ```

2. Import the schema and sample data from the repository root:

   ```sh
   mysql -u root -p < backend/database/easybites.sql
   ```

3. Create a local database account with CRUD-only permissions (replace the example password with a strong local password):

   ```sql
   CREATE USER 'easybites_app'@'127.0.0.1'
     IDENTIFIED BY 'replace_with_a_strong_local_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON easybites.*
     TO 'easybites_app'@'127.0.0.1';
   ```

4. Copy the safe configuration template and set the matching local database password:

   ```sh
   cp backend/config/config.example.php backend/config/config.php
   ```

   `backend/config/config.php` is ignored by Git. Never commit credentials or runtime log files.

5. Start the API server from the repository root:

   ```sh
   php -S 127.0.0.1:8000 -t backend/public backend/public/index.php
   ```

6. In another terminal, serve the SPA:

   ```sh
   php -S 127.0.0.1:5500 -t frontend
   ```

   Open `http://127.0.0.1:5500`. The weather service base URL is in the `easybites-api-base-url` meta tag in `frontend/index.html`. The API allows local frontend origins on ports 5500 and 8080.

The backend is not configured for production hosting. Its current CRUD endpoints have no authentication or authorization, and the frontend entity screens are not connected to them. Do not use this M3 state for public deployment or real customer data.

## API documentation

After starting the backend, open the interactive Swagger UI at [http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs). The OpenAPI JSON is generated from PHP 8.2 attributes and is available at [http://127.0.0.1:8000/openapi.json](http://127.0.0.1:8000/openapi.json) or `/docs/swagger.php`; its server URL follows the request host and base path. The Swagger UI assets load from a pinned CDN and require an internet connection.

CRUD endpoints are available under `/api/v1/` for `users`, `restaurants`, `menu-items`, `orders`, `order-items`, and `reviews`, with collection and record routes. The weather endpoint is `/api/v1/weather/sarajevo`. M3 does not implement authentication, so `401`/`403` authorization responses are not yet applicable.

## Verification

Run the isolated CRUD, validation, HTTP status, failure handling, OpenAPI-reference, and weather-stub integration checks:

```sh
php backend/tests/run.php
node --test frontend/tests/weather-service.test.js
```

The backend integration suite uses a temporary SQLite database; it does not replace testing against the project's MySQL/XAMPP configuration.

## Deployment and course submission

EasyBites is not deployed; no live URL or working test login exists. Pushes, pull requests, reviewer requests, merges, and LMS submissions are external steps and have not been performed by this repository setup.

See [AGENTS.md](AGENTS.md) for project-specific implementation rules, [docs/AI_LOG.md](docs/AI_LOG.md) for milestone AI-use notes, and [.agents/skills/update-ai-log/SKILL.md](.agents/skills/update-ai-log/SKILL.md) for the AI-log workflow.
