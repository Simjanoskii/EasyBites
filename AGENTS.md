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

- Work on **Milestone 1 only** unless the user explicitly requests a later milestone.
- Milestone 1 frontend screens and management controls are static previews. Restaurant, menu, order, user, and review records are mock data, not persisted.
- Do not imply that login, registration, checkout, admin actions, an API, or deployment works.
- Keep the user informed that the course topic/repository form and GitHub push or pull request are external submission steps; do not claim these were completed.

## Folder structure

- `frontend/`: browser application
  - `css/`: styles
  - `js/`: SPA navigation and interactions
  - `views/`: HTML view/template area
  - `static/`: static assets
  - `index.html`: current SPA shell and view sections
- `backend/`: PHP REST API
  - `routes/`, `services/`, `dao/`, `middleware/`, `config/`, `logs/`
- `docs/`: ERD and milestone/AI documentation
- `.github/agents/`: Frontend, Backend, and Reviewer custom agents
- `.agents/skills/`: reusable project skills

## Architecture and implementation rules

- Keep frontend files in `frontend/` and backend PHP in `backend/`. Never mix PHP into browser HTML, CSS, or JavaScript.
- The current SPA keeps its page sections in `frontend/index.html`; keep navigation client-side without full page reloads. Extract individual views only when explicitly asked and preserve the static preview behavior.
- Use no inline styles. Keep presentation in `frontend/css/`; prefer accessible semantic HTML and responsive CSS. Bootstrap 5 may be used where it fits the existing design.
- Keep browser-side sample data clearly identified as demo content. Do not add a frontend persistence workaround.
- Backend request flow: route -> service -> DAO -> database. Services call DAOs; routes do not call DAOs directly.
- Use PDO prepared statements for all SQL. Routes return JSON and appropriate HTTP status codes.
- Keep FlightPHP middleware reusable and registered with the framework.
- Keep secrets in ignored `backend/config/config.php`; commit/update only a safe configuration template. Never edit runtime files in `backend/logs/`.
- Keep the README and `docs/AI_LOG.md` current at every milestone; update `docs/ERD.md` when the planned schema changes.

## Conventions

- PHP classes: PascalCase; name class files after their class (for example, `UserService.php`).
- JavaScript/PHP variables and functions: camelCase.
- Database tables and columns: snake_case.
- Use clear English names, small scoped changes, and existing patterns. Explain changed files and verify relevant behavior.
- Do not add libraries, change the database schema, or broaden task scope without the user's approval.
