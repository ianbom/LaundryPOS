# Repository Guidelines

## Project Structure & Module Organization

This is a Laravel 13 application with an Inertia React frontend. Backend PHP code lives in `app/`, configuration in `config/`, routes in `routes/`, migrations/factories/seeders in `database/`, and Pest tests in `tests/Feature` and `tests/Unit`. Frontend source is in `resources/js`, with pages under `resources/js/pages`, layouts under `resources/js/layouts`, shared components under `resources/js/components`, hooks under `resources/js/hooks`, and types under `resources/js/types`. Blade entrypoints are in `resources/views`; public assets are served from `public/`.

## Build, Test, and Development Commands

- `composer setup`: install PHP and Node dependencies, create `.env`, generate app key, migrate, and build assets.
- `composer dev`: run Laravel server, queue listener, and Vite dev server together.
- `npm run dev`: start Vite only.
- `npm run build`: build production frontend assets.
- `composer test`: clear config, run Pint check, then run Laravel/Pest tests.
- `composer ci:check`: run frontend lint, formatting, type checks, and backend tests.
- `npm run types:check`: run TypeScript without emitting files.

## Coding Style & Naming Conventions

Use 4-space indentation, LF line endings, UTF-8, and final newlines as defined in `.editorconfig`. PHP follows Laravel Pint with the `laravel` preset. TypeScript/React uses Prettier with semicolons, single quotes, 80-column print width, and Tailwind class sorting via `prettier-plugin-tailwindcss`. Prefer PascalCase for React components, camelCase for hooks/utilities, and kebab-case or descriptive lowercase route names where Laravel conventions apply.

## Testing Guidelines

Tests use Pest with Laravel helpers. Place user-facing or HTTP behavior in `tests/Feature`, isolated logic in `tests/Unit`, and name files after the subject, for example `DashboardTest.php` or `AuthenticationTest.php`. Run focused tests with `php artisan test tests/Feature/DashboardTest.php`; run full verification with `composer test` before handing off changes.

## Commit & Pull Request Guidelines

Current Git history is minimal, so no strict commit convention is established. Use short, imperative commit subjects such as `Add dashboard metrics` or `Fix profile validation`. Pull requests should include a concise summary, test results, linked issue or task reference when available, and screenshots for UI changes under `resources/js`.

## Security & Configuration Tips

Do not commit `.env`, credentials, generated logs, or local storage files. Keep environment defaults in `.env.example`. For auth, Fortify, routes, or Inertia changes, verify both server behavior and frontend navigation.
