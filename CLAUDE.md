<!-- PROJECT TEMPLATE ─────────────────────────────────────────────────────
     Fill out the sections below before using Claude Code on this project.
     Remove placeholder lines that don't apply.

     CLAUDE: At the start of every session, check "Template status" below.
     If it is not "complete", stop and ask the user to fill out the template
     before doing anything else. Do not proceed until they confirm it is done
     or explicitly ask you to skip it.
──────────────────────────────────────────────────────────────────────── -->

## Project Overview
- **Site name**:
- **Drupal version**:
- **Purpose**: <!-- one sentence: what the site does and who it's for -->
- **Hosting**: <!-- e.g. Acquia Cloud, environment names -->

## Key Modules
<!-- Contrib or custom modules central to this project that Claude should know about -->
-

## Custom Modules
<!-- Custom modules with a one-line description of what each does -->
- `module_name` —

## Third-Party Integrations
<!-- External services the site connects to (CRM, marketing automation, search, etc.) -->
-

## Developer Preferences
<!-- Personal overrides — autonomy level, response style, commit style, test expectations -->
-

## Known Gotchas
<!-- Frozen areas, legacy quirks, things Claude should not touch or be careful around -->
-

## Template Status
- **Status**: incomplete <!-- Change to "complete" when all sections above are filled out -->

<!-- END TEMPLATE ──────────────────────────────────────────────────────── -->

# Elevated Third — Drupal Development Standards

## Before Making Changes
Before editing or creating any files, you must:
1. List every file you intend to create or modify
2. Provide a one sentence summary of what you are doing and why
3. Wait for explicit approval before proceeding

## Environment
- Running inside DDEV container
- Use `drush` directly (not `ddev drush` — already in container)
- Drupal webroot is `web/`
- Config sync dir is `config/sync/`
- Do not use auto or bypass mode — approval prompts must be respected

## Build & Install Commands
- **Install dependencies**: `composer install`
- **Lint**: check for `/phpcs.xml` or `/phpcs.xml.dist` first; if present run `phpcs`, otherwise `phpcs --standard=Drupal path/to/test`
- **Static analysis**: check for `/phpstan.neon` or `/phpstan.neon.dist` first; if present run `phpstan`, otherwise `phpstan analyse --level 6 path/to/test`
- **Run single test**: check for `/phpunit.xml` or `/phpunit.xml.dist` first; if present run `phpunit --filter Test path/to/test`, otherwise `phpunit -c web/core/phpunit.xml.dist --filter Test path/to/test`
- If phpcs/phpstan/phpunit are not available, install with `composer require --dev drupal/core-dev`

## Configuration Management
- **Export**: `drush config:export -y`
- **Import**: `drush config:import -y` then immediately `drush cache:rebuild`
- **Verify before importing**: `drush config:export --diff`
- **View config**: `drush config:get [config.name]`
- **Set config value**: `drush config:set [config.name] [key] [value]`
- **Partial import**: `drush config:import --partial --source=[path-to-module/config/install]`
- Always check config diffs before importing
- Always export config after making changes
- Never run `drush cache:rebuild` before `config:import` — run it after

## Development Commands
- **Clear cache**: `drush cache:rebuild`
- **List modules**: `drush pm:list [--filter=FILTER]`
- **List enabled modules**: `drush pm:list --status=enabled`
- **Require a module**: `composer require drupal/[module_name]`
- **Enable a module**: `drush en [module_name]`
- **Run database updates**: `drush updb`
- **Show status**: `drush status`
- **Inspect logs**: `drush watchdog:show --count=20`
- **Run cron**: `drush cron`
- **View entity fields**: `drush field:info [entity_type] [bundle]`

## Code Style Guidelines
- **PHP version**: 8.3+ compatibility required
- **Standard**: Drupal coding standards
- **Indentation**: 2 spaces, no tabs
- **Line length**: 120 characters maximum
- **Comment line length**: 80 characters maximum, always ending with a full stop
- **Namespaces**: PSR-4, `Drupal\{module_name}`
- **Types**: Strict typing with PHP 8 features, union types when needed
- **Documentation**: PHPDoc required for all classes and methods
- **Class structure**: Properties before methods, dependency injection via constructor
- **Naming**: CamelCase for classes/methods/properties, snake_case for variables, ALL_CAPS for constants
- **Error handling**: Specific exception types with `@throws` annotations and meaningful messages
- **Plugins**: Use Drupal plugin conventions with attributes for definition

## Best Practices
- Prefer contrib modules over replicating functionality in custom modules
- Module install config belongs in `config/install`, not `hook_install`
- If making config changes to a module's `config/install`, apply them to active config too
- Always run `drush updb` then `drush cache:rebuild` after composer updates

## Composer
- Always use `composer require` — never manually edit `composer.json`
- Check `composer outdated` before adding new dependencies
- Do not delete or regenerate `composer.lock` without approval

## Git
- Always write descriptive commit messages
- Never commit `settings.local.php`, `.env`, or generated files
- Check `git status` before committing to avoid unintended files
- Do not commit directly to `main` or `develop`
- Never merge or rebase without approval

## Acquia
- Never modify `.acquia/` or `hooks/` directories without approval
- Do not run Acquia CLI (`acli`) commands without approval
- Cloud hooks affect all environments — treat them as high risk

## What NOT to do
- Do not run `drush sql-drop` or `drush site-install`
- Do not use Drush remote aliases (`@prod`, `@staging`, etc.)
- Do not run `git push --force`
- Do not install modules outside of Composer
- Do not read or modify `.env` files, certificates, or key files
- Do not modify `web/sites/default/settings.php` or `settings.local.php`

## Session Behavior
- Re-read this file if unsure about any convention
- If a task feels outside these guidelines, stop and ask rather than assume
- Never assume approval — wait for an explicit "yes" or "go ahead"

## Reminders
- Always list files and get approval before making any changes
- Prompts exist for a reason — read them before approving