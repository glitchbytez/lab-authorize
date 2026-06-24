# LIS Authorization Portal

A multi-tenant Laboratory Information System (LIS) authorization portal. Lab scientists review and authorize diagnostic records from their assigned facility; administrators manage labs and users system-wide.

## Stack

- **Backend:** PHP 8+ (no framework, no Composer)
- **Database:** MySQL / MariaDB via PDO
- **Frontend:** Tailwind CSS (CDN), Lucide icons, vanilla JS
- **Auth:** PHP sessions with bcrypt password verification

## Quick start

```bash
# 1. Copy env and fill in credentials
cp .env.example .env

# 2. Create the database and seed it
mysql -u root -p < db/schema.sql
mysql -u root -p lis_gateway < db/seed.sql

# 3. Start the dev server
php -S localhost:8000
```

Default credentials (from seed):

| User | Password | Role |
|---|---|---|
| `admin-01` | `admin123` | Administrator |
| `S. Sibanda` | `password123` | Lab Scientist |

## Environment variables

```ini
API_DRIVER=database          # "database" or "external"
DB_HOST=localhost
DB_NAME=lis_gateway
DB_USER=your_db_user
DB_PASS=your_db_password

# Only needed when API_DRIVER=external
EXTERNAL_API_URL=https://example.com/api
EXTERNAL_API_KEY=your_api_key
```

## Architecture

### Entry points

| File | Purpose |
|---|---|
| `index.php` | Login POST handler + page router. Assembles views from `header.php`, `sidebar.php`, and `views/`. |
| `api/index.php` | JSON API gateway. All data operations from the browser hit this endpoint. |

### Driver pattern

All data operations are abstracted behind `ApiContract`. Switching to an external LIS API requires one `.env` change — no PHP edits.

```
API_DRIVER=database   → api/drivers/DatabaseDriver.php  (PDO, local MySQL)
API_DRIVER=external   → api/drivers/ExternalDriver.php  (cURL, third-party API)
```

`ApiClient::make()` is the factory that reads `API_DRIVER` and returns the correct driver instance.

```
api/
├── index.php               # Router: GET ?resource=  /  POST ?action=
├── bootstrap.php           # Requires all api/ classes in order
├── config.php              # env() helper — reads .env with system env fallback
├── Database.php            # PDO singleton
├── Auth.php                # requireLogin() / currentUser()
├── ApiClient.php           # Factory
├── contracts/
│   └── ApiContract.php     # Interface all drivers must satisfy
└── drivers/
    ├── DatabaseDriver.php
    └── ExternalDriver.php
```

### API surface

**GET** `api/index.php?resource=<resource>`

| Resource | Returns |
|---|---|
| `pending` | Records not yet Approved or Rejected, scoped to the user's lab |
| `completed` | Approved or Rejected records, scoped to the user's lab |
| `labs` | All registered client labs |
| `users` | All user accounts (no password data) |

**POST** `api/index.php?action=<action>` (JSON body)

| Action | Body fields |
|---|---|
| `create_lab` | `lab_name`, `ahfoz_number` |
| `delete_lab` | `lab_name` |
| `create_user` | `name`, `role`, `lab`, `password` |
| `delete_user` | `user_id` |
| `verify_record` | `accessionId`, `scientistNotes` |
| `reject_record` | `accessionId`, `scientistNotes` |
| `recheck_record` | `accessionId`, `scientistNotes` |

All responses are JSON. Errors return `{"error": "..."}` with an appropriate HTTP status code.

`api.js` wraps every call and throws on `data.error`.

### Session

`$_SESSION['user']` shape: `{id, name, role, lab}`. `lab` is `null` for Administrators and LIS Managers; lab scientists always have a lab assigned.

Login always authenticates against the local `users` table using `password_verify()`, regardless of `API_DRIVER`.

### Lab scoping

Records are filtered by the authenticated user's `lab`. Admins and LIS Managers (whose `lab` is `null`) see records from all facilities. Lab scientists only see records for their assigned lab. This is enforced in `api/index.php` by reading `Auth::currentUser()['lab']` and passing it through the contract to the driver's WHERE clause.

### Pages & access control

| URL | View | Roles |
|---|---|---|
| `?page=pending` | Authorization queue | All authenticated users |
| `?page=completed` | Released reports | All authenticated users |
| `?page=admin` | Labs & user management | `Administrator`, `LIS Manager` only |

Role enforcement for the admin page happens in `index.php`; the sidebar link is conditionally rendered in `sidebar.php`.

## Database schema

Four tables. See `db/schema.sql` for full DDL.

| Table | Purpose |
|---|---|
| `labs` | Registered client laboratories, each with an optional AHFOZ number |
| `users` | System users; `lab_name` FK to `labs`; passwords stored as bcrypt |
| `records` | Diagnostic records with status, ordering physician, and authorization info |
| `record_parameters` | Per-record test parameters (name, result, reference range, flag). Cascades delete from `records`. |

## Adopting an external LIS API

1. Set `API_DRIVER=external`, `EXTERNAL_API_URL`, and `EXTERNAL_API_KEY` in `.env`.
2. If the external API's response shape differs from the contract, adapt the mapping inside `ExternalDriver` only — no other files change.
3. Login still uses the local `users` table; only data operations are routed through the external driver.

## Production checklist

- [ ] Copy `.env.example` → `.env` and set real credentials
- [ ] Set `DB_USER` to a least-privilege account (not root)
- [ ] Ensure `.env` is never in version control (it is in `.gitignore`)
- [ ] Deploy behind Apache/Nginx — the `.htaccess` blocks direct access to `.env`, `db/`, and `.sql` files
- [ ] Use HTTPS; set `'secure' => true` in `session_set_cookie_params` in `config.php`
- [ ] Disable PHP `display_errors` in `php.ini` (`display_errors = Off`, `log_errors = On`)
