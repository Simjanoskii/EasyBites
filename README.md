# EasyBites

EasyBites is a local food-delivery platform for Sarajevo, Bosnia and Herzegovina. It is designed to help people discover and order from the city's restaurants and fast-food spots in one place.

> **Milestone 1 status:** This repository contains the project structure, responsive static single-page frontend for both planned roles, draft data model, and setup documentation. The frontend uses demonstration data only. A working REST API, authentication, database, Swagger UI, external API integration, and public deployment belong to later milestones.

## Project structure

```text
backend/
  config/       Configuration template and local secrets (not committed)
  dao/          PHP PDO data-access objects
  logs/         Runtime info and error logs (not committed)
  middleware/   Reusable FlightPHP middleware
  routes/       FlightPHP REST API routes
  services/     Backend business logic and integrations
docs/
  AI_LOG.md     AI usage and review notes by milestone
  ERD.md        Draft entity-relationship diagram
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

The initial database plan includes **Users, Restaurants, MenuItems, Orders, OrderItems, and Reviews**. A user can place many orders and write reviews; each restaurant owns menu items and receives reviews; each order contains one or more order items, each referring to a menu item. See the [draft ERD](docs/ERD.md).

## Roles and test accounts

The planned roles are **customer** and **admin**. The customer preview includes discovery, restaurant menus, basket, order tracking, profile, sign-in, and registration screens. The admin preview includes dashboard, restaurant and menu-item catalogues, order management, user roles, and review moderation. The current milestone has no backend or real accounts, so there are no working test credentials yet. The role preview and management controls are static; no account or record changes are saved.

## External API

An external API has not been integrated in this milestone. A future Sarajevo-relevant feature is planned for restaurant discovery, with the selected provider and its user-facing feature to be documented here once implemented.

## Run locally

No database import or configuration is needed for the static Milestone 1 preview.

1. Clone the repository.
2. Open `frontend/index.html` in a browser, or serve the repository with a local static web server and open the frontend entry point.
3. Use the profile page's **Open admin workspace** button to preview the admin screens. The admin preview uses mock data and does not require credentials.

In a later milestone, local setup instructions will include importing the provided SQL schema and copying the committed configuration template to a private local configuration file.

## API documentation

The FlightPHP REST API and its OpenAPI/Swagger documentation are not implemented yet. The Swagger documentation link will be added here when available.

## Deployment

EasyBites is not publicly deployed yet. The public application link will be added here after hosting is configured.

## AI usage

See [AGENTS.md](AGENTS.md) for project-specific coding guidance, [.github/agents/](.github/agents/) for the scoped Frontend/Backend/Reviewer agents, [.agents/skills/update-ai-log/SKILL.md](.agents/skills/update-ai-log/SKILL.md) for the reusable AI-log workflow, and [docs/AI_LOG.md](docs/AI_LOG.md) for the Milestone 1 AI usage log.

## Course submission

Submit the project topic and this GitHub repository URL through the course/LMS form. This repository setup does not submit the form on your behalf.
