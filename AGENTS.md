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
- `backend/openapi.json` documents every implemented CRUD endpoint, the Sarajevo weather endpoint, and the documentation endpoints. `/docs` serves Swagger UI and `/openapi.json` serves the specification.
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
  - `routes/`: FlightPHP route registration and request/response helpers
  - `services/`: business validation, DAO orchestration, logging, weather provider
  - `dao/`: PDO data access
  - `middleware/`: reserved for later milestones
  - `config/`, `logs/`
  - `database/`: reproducible MySQL schema and sample data
  - `tests/`: isolated SQLite-backed HTTP integration suite
  - `openapi.json`: endpoint and schema documentation
- `composer.json` / `composer.lock`: FlightPHP dependency and PHP class autoloading
- `docs/`: ERD and milestone/AI documentation
- `.github/agents/`: Frontend, Backend, and Reviewer custom agents
- `.agents/skills/`: reusable project skills

## Architecture and implementation rules

- Keep frontend files in `frontend/` and backend PHP in `backend/`. Never mix PHP into browser HTML, CSS, or JavaScript.
- The current SPA keeps its page sections in `frontend/index.html`; keep navigation client-side without full page reloads. Extract individual views only when explicitly asked and preserve the static preview behavior.
- Use no inline styles. Keep presentation in `frontend/css/`; prefer accessible semantic HTML and responsive CSS. Bootstrap 5 may be used where it fits the existing design.
- Keep browser-side sample data clearly identified as demo content. The only M3 backend-connected UI feature is the weather card; do not add a frontend persistence workaround or imply the other demo screens persist.
- Keep backend HTTP calls in frontend service modules. The M3 weather request belongs in `frontend/js/services/weather-service.js`.
- Backend request flow: route -> service -> DAO -> database. Services call DAOs; routes do not call DAOs directly.
- Use PDO prepared statements for all SQL. Routes return JSON and appropriate HTTP status codes.
- Keep SQL identifiers fixed in DAO configuration and validate writable columns against each DAO's allowlist. Business validation belongs in the services milestone.
- Register FlightPHP routes in `backend/routes/ApiApplication.php`; keep HTTP handlers small and ensure unexpected failures return generic JSON without SQL, stack traces, credentials, or filesystem paths.
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
