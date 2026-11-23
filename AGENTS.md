# EasyBites project guidance

## Project

EasyBites is a local food-delivery web application for customers and restaurant partners in Sarajevo, Bosnia and Herzegovina. Planned entities are Users, Restaurants, MenuItems, Orders, OrderItems, and Reviews. Planned roles are customer and admin.

## Technology stack

- Frontend: HTML, CSS, and JavaScript single-page application (SPA).
- Backend: PHP REST API using FlightPHP, PDO, and MySQL.
- Local development target: XAMPP.
- API documentation: OpenAPI.
- Libraries such as Bootstrap may be used when helpful; do not add dependencies without approval.

## Current milestone

- Work on **Milestone 3: services, REST routes, OpenAPI, and external API**. The six PDO DAOs are in `backend/dao/`; business validation and orchestration are in `backend/services/`; FlightPHP routes and HTTP handling are in `backend/routes/`; the app entry point is `backend/public/index.php`.
- Services are the only layer that calls DAOs. Keep SQL in DAOs, validate application input in services, and keep routes focused on request parsing, response serialization, and HTTP status codes.
- `zircote/swagger-php` generates the OpenAPI 3 document from PHP 8.2 attributes in the route classes and `backend/public/v1/docs/doc_setup.php`. `/docs` serves Swagger UI, while `/docs/swagger.php` and `/openapi.json` serve the generated specification with a request-aware server URL.
- The Sarajevo weather card on the Discover page calls the backend through `frontend/js/services/weather-service.js`. It uses Open-Meteo without an API key and shows a retry/fallback when weather is unavailable.
- Restaurant, menu, order, user, and review content in the other SPA screens remains demonstration data. Do not claim those screens are backed by the API.
- Authentication, JWTs, role authorization, reusable auth/request/error middleware, full entity AJAX workflows, and deployment belong to later milestones. The M3 API has no authentication and must only be run on a local development server; do not expose it to the internet.
- The API creates customer users with server-hashed passwords and never accepts or returns password hashes. Do not add a role-management API before server-side authorization exists.
- Keep the user informed that GitHub push, pull request, merge, deployment, and LMS submission are external steps; do not claim these were completed.

## Folder structure

- `frontend/`: browser application
  - `css/`: styles
  - `js/`: SPA navigation and interactions
  - `views/`: HTML view/template area
  - `static/`: static assets
  - `index.html`: current SPA shell and view sections
- `backend/`: PHP REST API
  - `public/`: FlightPHP entry point and Apache rewrite configuration
  - `routes/`: FlightPHP bootstrap, per-entity route registration, and request/response helpers
  - `services/`: business validation, DAO orchestration, logging, weather provider
  - `dao/`: PDO data access
  - `middleware/`: reserved for later milestones
  - `config/`, `logs/`
  - `database/`: reproducible MySQL schema and sample data
  - `tests/`: isolated SQLite-backed HTTP integration suite
  - `public/v1/docs/`: PHP-attribute OpenAPI metadata and Swagger UI page
- `composer.json` / `composer.lock`: FlightPHP and swagger-php dependencies, PHP version requirement, and class autoloading
- `docs/`: ERD and milestone/AI documentation
- `.github/agents/`: Frontend, Backend, and Reviewer custom agents
- `.agents/skills/`: reusable project skills

## Architecture and implementation rules

- Keep frontend files in `frontend/` and backend PHP in `backend/`. Never mix PHP into browser HTML, CSS, or JavaScript.
- The SPA uses one `frontend/index.html` shell with empty page sections and separately loaded fragments in `frontend/views/`. Keep hash/history navigation client-side without full page reloads, retain the not-found view, and preserve the static preview behavior. Shared navigation and interaction handlers must continue to work after fragments load.
- Use no inline styles. Keep presentation in `frontend/css/`; prefer accessible semantic HTML and responsive CSS. Bootstrap 5.3.3 is loaded as the base stylesheet; preserve the existing custom responsive theme and mobile navigation behavior.
- Keep browser-side sample data clearly identified as demo content. The only M3 backend-connected UI feature is the weather card; do not add a frontend persistence workaround or imply the other demo screens persist.
- Keep backend HTTP calls in frontend service modules. The M3 weather request belongs in `frontend/js/services/weather-service.js`.
- Backend request flow: route -> service -> DAO -> database. Services call DAOs; routes do not call DAOs directly.
- Use PDO prepared statements for all SQL. Routes return JSON and appropriate HTTP status codes.
- Keep SQL identifiers fixed in DAO configuration and validate writable columns against each DAO's allowlist. Business validation belongs in the services milestone.
- Use `backend/routes/ApiApplication.php` only to wire the app. Keep CRUD route declarations in the matching per-entity `*Routes.php` class and share generic CRUD plumbing through `ApiRoutes.php`. Use numeric ID route constraints and JSON not-found/method-not-allowed handlers.
- Keep HTTP handlers small and ensure failures return generic JSON without SQL, stack traces, credentials, or filesystem paths. `ApiResponse` delegates caught route failures to `ApiErrorHandler`.
- Keep review validation in `ReviewService`: reject HTML tags, require a delivered order by the same user at the restaurant, and check duplicate reviews before DAO writes.
- Keep OpenAPI metadata in PHP 8.2 attributes; do not restore or edit a hand-maintained JSON specification. Add endpoint attributes alongside each route and maintain component schemas, responses, and request bodies in `backend/public/v1/docs/doc_setup.php`.
- The Composer stack requires PHP 8.2 or newer because swagger-php 6 uses PHP 8 attributes.
- Keep FlightPHP middleware reusable and registered with the framework when implementing a later milestone.
- Give third-party requests a finite timeout, validate status and response shape, log success/failure, and return a clear service-unavailable response without breaking the rest of the SPA.
- Keep secrets in ignored `backend/config/config.php`; commit/update only a safe configuration template. Never edit runtime files in `backend/logs/`.
- Keep the README and `docs/AI_LOG.md` current at every milestone; update `docs/ERD.md` when the planned schema changes.

## Conventions

- PHP classes: PascalCase; name class files after their class (for example, `UserService.php`).
- JavaScript/PHP variables and functions: camelCase.
- Database tables and columns: snake_case.
- Use clear English names, small scoped changes, and existing patterns. Explain changed files and verify relevant behavior.
- Do not add libraries or broaden task scope without the user's approval. Change the database schema only when explicitly requested.
- Run backend integration checks with `php backend/tests/run.php`, frontend service tests with `node --test frontend/tests/weather-service.test.js`, and validate the Composer manifest before handing off.
