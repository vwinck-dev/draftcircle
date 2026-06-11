# DraftCircle

> Write together. Publish boldly. Find your readers.

DraftCircle is a social writing platform where authors create, publish, discuss, and discover
books together.

The project is evolving from a complete digital library foundation into a community-centered
product for writers and readers. Its current release already provides discovery, digital reading,
ratings, threaded discussions, notifications, moderation, and administration. The next product
phase will introduce author profiles, book creation, drafts, chapters, publishing workflows,
follows, feeds, and collaborative feedback.

## Product Vision

DraftCircle should make writing feel social without making it noisy.

- Writers can develop books from early drafts to published editions.
- Readers can discover stories, follow authors, react, discuss, and provide useful feedback.
- Communities can form around genres, books, and shared creative interests.
- Authors retain a clear workspace while gaining a direct relationship with readers.

## Current Capabilities

### Discovery and reading

- Searchable catalog with categories, formats, availability, and recommendations.
- Responsive home, catalog, book details, and dedicated digital reader.
- Reading progress, ratings, comments, replies, and comment voting.
- Personalized recommendations with explicit cookie consent.

### Community and communication

- User profiles and preferences.
- Persistent in-app and email notifications.
- Real-time notification delivery through Laravel Reverb.
- Comment moderation, blocked-word rules, soft deletion, and staff tools.

### Operations

- Reservations, loans, returns, overdue processing, and fines.
- Administrative dashboards, reports, settings, users, books, and moderation.
- Redis-backed queues and cache, Horizon, Telescope, scheduled jobs, and health checks.
- English and Brazilian Portuguese localization, with English as the default language.

## Roadmap

The existing library domain remains useful as the discovery and reading layer. Planned social
writing capabilities include:

1. Public author profiles, follows, and social graphs.
2. Personal writing studios with books, chapters, drafts, and revision history.
3. Publishing controls for private drafts, beta readers, serialized releases, and completed books.
4. Activity feeds for followed authors, new chapters, comments, and recommendations.
5. Collaborative feedback, annotations, writing circles, and co-author workflows.
6. Creator analytics, reading lists, collections, and community discovery.

## Technology

### Backend

- PHP 8.3 and Laravel 11
- Inertia.js 2
- MySQL 8
- Redis 7 with Predis
- Laravel Horizon, Reverb, Sanctum, Socialite, and Telescope

### Frontend

- Vue 3 with Composition API
- TypeScript
- Vite
- Tailwind CSS
- Pinia
- Headless UI and Lucide icons

### Quality and infrastructure

- Docker Compose and Nginx
- PHPUnit 11 and ParaTest
- Larastan/PHPStan level 9
- Laravel Pint
- ESLint, Prettier, and vue-tsc
- GitHub Actions, Husky, lint-staged, and commitlint

## Architecture

DraftCircle organizes business behavior into explicit, testable use cases.

```text
HTTP routes
  -> middleware and policies
  -> controllers
  -> application services
  -> actions
  -> Eloquent models
  -> events, listeners, notifications, cache, and queues
```

- Controllers translate HTTP requests into application calls.
- Services coordinate related capabilities.
- Actions implement small use cases.
- DTOs and enums keep application boundaries typed.
- Policies and form requests centralize authorization and validation.
- Events and queued listeners isolate side effects.
- Observers keep cached views consistent.

## Local Development

### Requirements

- Docker with Docker Compose
- Git

### Start the environment

```bash
cp .env.example .env
docker compose build
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed --force
```

Default local URLs:

| Service | URL |
| --- | --- |
| DraftCircle | `http://localhost` |
| Mailpit | `http://localhost:8025` |
| Reverb | `ws://localhost/app/{key}` |

Default seeded accounts:

| Role | Login | Password |
| --- | --- | --- |
| Administrator | `admin@draftcircle.local` | `password` |
| Editor | `editor@draftcircle.local` | `password` |
| Writer | `writer1@draftcircle.local` or `STU0001` | `password` |

## Commands

### Backend quality

```bash
docker compose exec app composer check
```

This runs Pint, PHPStan, and the parallel PHPUnit suite.

### Frontend quality

```bash
docker compose exec vite npm run lint
docker compose exec vite npm run build
```

### Tests

```bash
docker compose exec app php artisan test
docker compose exec app php artisan test --parallel
```

Tests force SQLite in memory, array-backed cache and sessions, synchronous queues, and disabled
Telescope so they never modify development services.

### Existing operational commands

```bash
docker compose exec app php artisan library:send-overdue-notifications
docker compose exec app php artisan library:clean-expired-reservations
docker compose exec app php artisan library:generate-report --period=weekly
docker compose exec app php artisan library:process-pending-fines
docker compose exec app php artisan library:warm-cache
```

These command names remain stable while the product evolves because they represent the current
circulation domain rather than the DraftCircle brand.

## Production

Create `.env.production` from `.env.production.example`, replace every secret, and run:

```bash
docker compose --env-file .env.production -f compose.prod.yaml build
docker compose --env-file .env.production -f compose.prod.yaml run --rm migrate
docker compose --env-file .env.production -f compose.prod.yaml up -d
```

Migrations are explicit and never run automatically during container startup. Back up the MySQL
and application storage volumes before significant updates.

## Localization

English is the default language. Brazilian Portuguese remains available through the locale
switcher. Application text must use `trans()` and every translation key must exist in both
`lang/en` and `lang/pt_BR`.

## Security and Privacy

- CSRF protection, encrypted sessions, authorization policies, and rate limiting.
- Optional secure cookies and same-site controls.
- Explicit personalization and analytics cookie consent.
- Personal data erasure workflow.
- Queue, observability, and administration tools restricted by role.

## Author

Developed by [vwinck-dev](https://github.com/vwinck-dev).

## License

DraftCircle is open-source software licensed under the MIT license.
