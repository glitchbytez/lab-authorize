# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running the app

No build step. Serve with PHP's built-in server:

```bash
php -S localhost:8000
```

Before starting, copy `.env.example` to `.env` and fill in your database credentials, then run:

```bash
mysql -u root -p < db/schema.sql
mysql -u root -p lis_gateway < db/seed.sql
```

Default credentials (from seed):
- Admin: `admin-01` / `admin123`
- Scientist: `S. Sibanda` / `password123`

## Architecture

A multi-tenant Laboratory Information System (LIS) authorization portal for Zimbabwe.
Database is MySQL/MariaDB accessed via PDO. All credentials come from `.env` (never hard-coded).

### Request flow

Two entry points share the same session:

1. **`index.php`** — traditional form-POST MVC. Handles login and (as a fallback) admin CRUD. Includes partial PHP views from `views/` assembled with `header.php` and `sidebar.php`. Also contains the SPA navigation layer: a `fetch`-based JS listener that swaps `<main>` content without full-page reloads.

2. **`api/index.php`** — JSON gateway. The browser's JS (`api.js`) calls this exclusively for all data operations. It delegates every request to `ApiClient::make()`.

### Driver pattern (`api/`)

The entire backend is abstracted behind `api/contracts/ApiContract.php`. Switching backend requires one `.env` change:

```
API_DRIVER=database   # uses DatabaseDriver — PDO against local MySQL
API_DRIVER=external   # uses ExternalDriver — cURL against a third-party LIS API
```

```
api/
├── index.php               # Router (GET ?resource=, POST ?action=)
├── bootstrap.php           # Requires all api/ classes in order
├── config.php              # env() helper — reads .env with system env fallback
├── Database.php            # PDO singleton
├── Auth.php                # Session guard (requireLogin / currentUser)
├── ApiClient.php           # Factory: ApiClient::make() → ApiContract instance
├── contracts/
│   └── ApiContract.php     # Interface all drivers must satisfy
└── drivers/
    ├── DatabaseDriver.php  # PDO implementation
    └── ExternalDriver.php  # cURL implementation
```

To adopt an external API: set `API_DRIVER=external` + `EXTERNAL_API_URL` + `EXTERNAL_API_KEY` in `.env`. No PHP code changes required. If the external API's response shape differs from the contract, adapt inside `ExternalDriver` only.

### API surface

- `GET  api/index.php?resource=pending|completed|labs|users`
- `POST api/index.php?action=create_lab|delete_lab|create_user|delete_user|verify_record|reject_record|recheck_record`

`api.js` wraps every call. Throws on `data.error`.

### Session

`$_SESSION['user']` holds the logged-in user (`id`, `name`, `role`, `lab`). Login always authenticates against the local `users` table with `password_verify()`, regardless of `API_DRIVER`. The DB is not queried on every request.

### Pages and role-gating

| URL | View | Access |
|---|---|---|
| `?page=pending` | Authorization queue | All logged-in users |
| `?page=completed` | Released reports | All logged-in users |
| `?page=admin` | System management | `Administrator` or `LIS Manager` only |

`views/admin.php` manages labs and users entirely through `api.js` — the form-POST handlers in `index.php` for those actions exist as a fallback only.

### Database schema

Four tables: `labs`, `users`, `records`, `record_parameters`. See `db/schema.sql`. `record_parameters` normalizes the per-record test parameters and cascades deletes from `records`. All passwords are bcrypt via `PASSWORD_BCRYPT`.
