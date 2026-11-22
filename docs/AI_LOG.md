# AI Log

## Milestone 1 — Initial setup, static frontend, and database planning

### Tools and tasks

- **Tool:** Copilot SDK in VS Code.
- **Model:** Not recorded in the available conversation details.
- **Task:** Created the project structure, responsive static customer SPA, sample restaurant and basket interactions, admin role preview, draft six-entity ERD, README, and AI guidance files.
- **Important prompts (excerpts):**
  - “this is my current task and milestones, please do this ...” with the EasyBites Sarajevo food-delivery description and Milestone 1 requirements.
  - “if not do everytinhg that is left, also please do not forget to add the needed stuff into AGENTS”
- **Required Frontend-agent task:** “In frontend/ only, improve keyboard accessibility of the admin workspace tabs: support Left/Right/Home/End keyboard navigation with focus and correct aria-selected state; ensure tab and panel relationships use appropriate IDs, aria-controls, aria-labelledby, and tabpanel semantics. Preserve current visual design, SPA behavior, and static demo labels.”
- **Required Reviewer pass:** The read-only Reviewer confirmed the tab changes had no rule violations, then found the basket/profile hash links were not intercepted, the mobile menu label did not reflect its expanded state, and favorite buttons lacked accessible saved state. A follow-up found that favorite state reset on filtering. These findings were corrected and exercised in the browser.
- **Kept and changed:** Kept the static SPA, project structure, and ERD. After reviewing the milestone scope, added an admin workspace and completed the registration form preview. All restaurant/order/admin records and actions remain explicitly demo-only.

### Where the AI was wrong

1. **The initial frontend omitted the admin role experience.** The first preview covered customer discovery and ordering but had no admin dashboard or management views. Comparing the screens to the planned customer/admin roles showed the gap; a static admin workspace was added for overview, restaurants, menu items, orders, users, and reviews.
2. **The initial registration preview omitted the user's name.** The form only showed email and password, which was incomplete for the registration screen. Reviewing the page against the expected registration flow exposed this; a required name field was added for registration and hidden/disabled as a requirement during sign-in.
3. **An early AI-log draft described a hypothetical mistake rather than an observed one.** Reviewing it against the actual work showed it was not evidence-based. It was replaced with the concrete omissions above and their fixes.
4. **The initial SPA anchor handler only covered navigation links and the brand.** Reviewer testing exposed that the basket and avatar links changed the fragment without switching views. The handler now routes all supported page fragments, and browser checks confirmed basket/profile navigation.
5. **The initial favorite buttons had no accessible pressed state, and rendering used only temporary button state.** Reviewer checks exposed missing screen-reader state and resets after filtering. Favorites now use a session-local set, expose `aria-pressed` and an updated label, and retain state when results rerender.
6. **The mobile menu's accessible label stayed “Open navigation” while expanded.** The Reviewer noticed the label did not match the open state. The label now changes to “Close navigation” when expanded and resets when the menu closes.

**Verification:** Checked JavaScript syntax and whitespace, verified that local configuration/log/vendor paths are ignored, and exercised customer basket/profile navigation, admin section keyboard navigation and ARIA relationships, favorites through filtering, mobile menu state, registration mode, and mobile layout in a browser.

## Milestone 2 — MySQL schema and DAO layer

### Tools and tasks

- **Tool:** Copilot SDK in VS Code.
- **Model:** Not recorded.
- **Task:** Added the six-entity MySQL schema and sample restaurant/menu data, PDO configuration loading and a safe configuration template, reusable prepared-statement CRUD operations, DAOs for all six entities, and Milestone 2 setup/schema documentation.
- **Important prompt:** “now it is time for assignment milestone 2, can you do it, do everything it needs and do not make mistakes” with the supplied course project and milestone specification.
- **Kept and changed:** Kept the planned Users, Restaurants, MenuItems, Orders, OrderItems, and Reviews entities. Refined the ERD with MySQL keys/constraints and implemented the corresponding schema and DAOs. The frontend remains a static preview; no routes or services were added because those belong to later milestones.

### Where the AI was wrong

1. **The first `BaseDAO` draft used `PDO::query()` for `findAll()` instead of a prepared statement.** Comparing the implementation with the Milestone 2 requirement for prepared-statement CRUD exposed it. `findAll()` was changed to call `prepare()` and `execute()`.
2. **The existing Backend agent instructions said to stop rather than edit documentation outside `backend/`.** This conflicted with the requirement to update milestone documentation. Reviewing the task against those instructions exposed the conflict; the agent guidance was changed to allow directly related milestone documentation while keeping backend implementation in `backend/`.

**Verification:** PHP lint passed for all backend PHP files using XAMPP PHP 8.0.28. Imported the schema and sample data into a temporary isolated database on XAMPP MariaDB 10.4.28, re-imported it to verify sample-data idempotence, and exercised create/read/update/delete through all six DAOs. Also verified the PDO configuration helper, native prepared-statement setting, invalid-field and invalid-ID handling, database constraints, unique keys, foreign keys, delete restrictions, missing-record behavior, sample counts, and cleanup. Then imported the schema into the local `easybites` database and configured a local-only app account with a random password stored only in ignored `backend/config/config.php`; verified all six DAOs' CRUD operations through this account, its CRUD-only grants, and that it cannot create schema objects. Neither the credential nor account password is in the repository. Static checks confirmed six keyed tables, seven valid foreign-key references, six DAO mappings, ignored local config, and clean whitespace. The local MySQL 9.1 CLI was not used because its authentication plugin was unavailable; testing used XAMPP's bundled client and server instead.

## Milestone 3 — Services, REST API, OpenAPI, and external API

### Tools and tasks

- **Tools:** GitHub Copilot App (Auto mode; concrete model not recorded), PHP 8.5.12 CLI and built-in web server, Composer 2.11.1, FlightPHP 3.19.5, PDO SQLite for isolated HTTP integration tests, MySQL 9.1.0 and PDO MySQL for isolated schema/DAO/API verification, Node.js 24.16.0, Python's JSON parser, and Git.
- **Task:** Added validated service classes for all six entities; FlightPHP JSON CRUD endpoints; safe error mapping, password hashing/redaction, and M3-local logging; OpenAPI 3 documentation and a Swagger UI route; and the Open-Meteo weather-aware dining feature in the Discover SPA. Kept other entity screens and operations demo-only.
- **Important prompt (verbatim excerpt):** “okay can you now do Milestone 3, and before you tell me it is done, check as many times as possible, you need to check and make sure everythin works, take you time, you have 2 hours to do it”
- **Audit prompt (verbatim):** “please check even a million times if you have to if everything from milestones 1,2,3 is functioning and fix if something isn't right or isn't functioning”
- **Kept and changed:** Kept all six M2 tables and DAOs. Added Composer/FlightPHP, services and routes, a local-only API front controller, API documentation, tests, and a frontend service that calls only the weather endpoint. No authentication, role authorization, full entity AJAX connection, deployment, push, or pull request was added.

### Where the AI was wrong or produced poor output

1. **The first OpenAPI JSON draft had missing closing braces.** `python3 -m json.tool` rejected it at end-of-file before any docs were accepted. The missing braces were fixed, then JSON parsing and internal component-reference checks passed.
2. **The Composer lockfile was stale after adding PHP and classmap requirements to `composer.json`.** `composer validate` detected the mismatch; `composer update --lock` synchronized it, and Composer validation then passed.
3. **The first PHP built-in-server integration run returned 404 for every `/api/v1/...` endpoint.** The server's `SCRIPT_NAME` made Flight treat `/api/v1` as the application base and strip it from each request URL. Inspection of the actual request path exposed the issue; the CLI-server entry point now sets the script name to `/index.php`, and the CRUD HTTP suite then passed.
4. **The initial weather-failure test used a static call counter in its test server fixture.** The test server's PHP request lifecycle did not preserve that counter between requests, so it could not deterministically produce a second-call outage. The fixture was changed to select success, failure, malformed data, and timeout simulations through an explicit test-only query mode; all outage cases then passed.
5. **The first browser weather check showed an outage even though the production endpoint worked.** The isolated HTTP test router omitted production's allowed-origin CORS headers. Comparing the fixture with `backend/public/index.php` exposed the mismatch; the fixture now mirrors the configured local origins, and tests cover allowed/disallowed origins and the weather response.
6. **Restaurant detail navigation did not fully support direct links or browser history, and SPA navigation ignored reduced-motion preferences.** Browser testing exposed the blank `#restaurant` reload and history behavior. Restaurant cards now use semantic links, detail navigation restores on direct load and browser history, focuses the heading, and respects reduced motion.

**Verification:** All backend PHP files passed lint; all frontend JavaScript passed syntax checks and the three weather-service tests passed. The isolated SQLite/FlightPHP suite passed 69 checks, including all six entities' CRUD, validation, CORS, error responses, weather outage/malformed/timeout cases, Swagger delivery, OpenAPI coverage, and component references. Against a private temporary MySQL 9.1 instance, the schema imported twice and the six DAOs passed transactional CRUD/allowlist/constraint checks (26 assertions); the production FlightPHP entry point then passed 26 route checks across all six entities, including CRUD, CORS, password redaction, and 404s. The live Open-Meteo endpoint returned Sarajevo conditions and the SPA rendered them; provider failure cases were tested with stubs. Swagger UI was opened in the browser, and the SPA's navigation, basket, favorites, admin tabs, weather fallback/retry, and responsive layouts from 320–1440px were exercised. Composer validation passed with its existing missing-license notice; Composer audit found no advisories. The regular XAMPP configuration was absent, so these database checks used only the isolated temporary server; no XAMPP instance was tested. All temporary servers/configuration were removed. The M3 work is committed as `bf75dd8` and pushed to `origin/simjanoskii-milestone3`; no PR or merge exists.
