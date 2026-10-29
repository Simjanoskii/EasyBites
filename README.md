# EasyBites

EasyBites is a local food-delivery platform for Sarajevo, Bosnia and Herzegovina. It is designed to help people discover and order from the city's restaurants and fast-food spots in one place.

> **Milestone 2 status:** The repository contains the responsive static SPA preview, a six-entity MySQL schema with sample restaurant/menu data, and PDO DAO classes with reusable prepared-statement CRUD operations. The browser still uses demonstration data: REST routes, authentication, Swagger UI, external API integration, and public deployment are later milestones.

## Project structure

```text
backend/
  config/       Configuration template and local secrets (not committed)
  database/     Reproducible MySQL schema and sample data
  dao/          PHP PDO data-access objects
  logs/         Runtime info and error logs (not committed)
  middleware/   Reusable FlightPHP middleware
  routes/       FlightPHP REST API routes
  services/     Backend business logic and integrations
docs/
  AI_LOG.md     AI usage and review notes by milestone
  ERD.md        Entity-relationship diagram for the current schema
.github/
  agents/       Frontend, Backend, and read-only Reviewer agents
.agents/
  skills/       Reusable update-ai-log workflow
frontend/
  css/          Responsive application styles
  js/           SPA navigation and demo interactions
  static/       Static assets (directory ready for project assets)
  views/        Reserved for extracted HTML templates
  index.html    SPA entry point and current page templates
```

## Entities and relationships

The database contains **Users, Restaurants, MenuItems, Orders, OrderItems, and Reviews**. A user can place many orders and write reviews; each restaurant owns menu items and receives reviews; each order contains order items that refer to menu items. Order items preserve the purchase-time price. See the [schema ERD](docs/ERD.md) and [MySQL export](backend/database/easybites.sql).

The `BaseDAO` provides `create`, `findById`, `findAll`, `update`, and `delete` methods using PDO prepared statements. `UserDAO`, `RestaurantDAO`, `MenuItemDAO`, `OrderDAO`, `OrderItemDAO`, and `ReviewDAO` each supply their table and writable-column allowlist.

## Roles and test accounts

The planned roles are **customer** and **admin**. The customer preview includes discovery, restaurant menus, basket, order tracking, profile, sign-in, and registration screens. The admin preview includes dashboard, restaurant and menu-item catalogues, order management, user roles, and review moderation. There are no working test credentials yet; users are not seeded in the SQL export, and the role preview and management controls remain static.

## External API

An external API has not been integrated yet. The provider and user-facing feature will be documented here in the milestone that implements it.

## Run locally

The frontend preview can still be opened without starting a backend. To create the Milestone 2 database and prepare local PDO configuration:

1. Install PHP 7.4 or newer with the PDO MySQL extension, and MySQL 8.0.16 or newer (the schema uses enforced `CHECK` constraints). The schema was also verified with XAMPP's MariaDB 10.4.28.
2. Clone the repository and import the SQL file from the repository root:

   ```sh
   mysql -u root -p < backend/database/easybites.sql
   ```

   The script creates and selects the `easybites` database, its tables, and sample restaurants/menu items. For a restricted local MySQL account, grant that account access to this database.
3. Create a dedicated local database account with CRUD-only permissions. Replace the example password with a strong local password:

   ```sql
   CREATE USER 'easybites_app'@'127.0.0.1'
     IDENTIFIED BY 'replace_with_a_strong_local_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON easybites.*
     TO 'easybites_app'@'127.0.0.1';
   ```

   Run these statements in the local MySQL/MariaDB shell as an administrator. The account is only for connections from this computer; do not enable remote database access for course marking.
4. Copy the safe configuration template and set the same password:

   ```sh
   cp backend/config/config.example.php backend/config/config.php
   ```

   Keep `backend/config/config.php` private; it is ignored by Git. The database import is run separately as an administrator, so the app account does not need schema-creation privileges.
5. Open `frontend/index.html` in a browser, or serve the repository with a local static web server and open the frontend entry point. The current browser screens use mock data and are not connected to these DAOs.

No backend API server, service layer, or routes are provided in this milestone, so the DAOs cannot yet be exercised through the SPA.

## API documentation

The FlightPHP REST API and its OpenAPI/Swagger documentation are not implemented yet. The Swagger documentation link will be added here when available.

## Deployment

EasyBites is not publicly deployed yet. The public application link will be added here after hosting is configured.

## AI usage

See [AGENTS.md](AGENTS.md) for project-specific coding guidance, [.github/agents/](.github/agents/) for the scoped Frontend/Backend/Reviewer agents, [.agents/skills/update-ai-log/SKILL.md](.agents/skills/update-ai-log/SKILL.md) for the reusable AI-log workflow, and [docs/AI_LOG.md](docs/AI_LOG.md) for AI usage notes by milestone.

## Course submission

Submit the project topic and this GitHub repository URL through the course/LMS form. This repository setup does not submit the form on your behalf.
