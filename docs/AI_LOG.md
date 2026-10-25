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
