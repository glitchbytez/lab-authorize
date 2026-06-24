# Real API Implementation Plan

Migrating from session-based mock storage to a real MySQL/MariaDB database, with all API logic
moved into a dedicated `api/` directory. The architecture uses a driver pattern so that swapping
from the internal database to any external LIS API requires only a single config change — no
controller or router code is touched.

---

## Target directory structure

```
api/
├── index.php                       # Router — replaces root api.php
├── config.php                      # Reads .env; exposes DB credentials and API_DRIVER
├── Database.php                    # PDO singleton (used by DatabaseDriver only)
├── Auth.php                        # Session guard shared by router and index.php
├── ApiClient.php                   # Factory: returns the configured driver instance
├── contracts/
│   └── ApiContract.php             # Interface all drivers must implement
└── drivers/
    ├── DatabaseDriver.php          # Implements ApiContract via PDO
    └── ExternalDriver.php          # Implements ApiContract via cURL (external LIS API)

db/
├── schema.sql                      # Table definitions
└── seed.sql                        # Default labs, users, and sample records

.env.example                        # Credential template (commit this, not .env)
.env                                # Actual credentials (git-ignored)
```

Root `api.php` and `ApiClient.php` are deleted once migration is complete.
`data.php` is already orphaned — delete it now.

---

## The driver pattern

The entire backend is abstracted behind a single interface. The router and `index.php` form
handler never call a driver directly — they always go through the factory:

```php
// api/index.php and index.php both do this:
$api = ApiClient::make();
$api->getPendingRecords();
$api->createLab($name, $ahfoz);
// etc.
```

`ApiClient::make()` reads `API_DRIVER` from the environment and returns the right instance:

```
API_DRIVER=database   → DatabaseDriver   (PDO, your own DB)
API_DRIVER=external   → ExternalDriver   (cURL, third-party LIS API)
```

Switching backends in production = one line in `.env`. No PHP files change.

---

## `ApiContract` — the shared interface

Every method that exists today in `ApiClient.php` becomes a method on this interface.
Both drivers must satisfy it exactly, including return shapes, so the router stays identical
regardless of which driver is active.

```php
interface ApiContract
{
    // Records
    public function getPendingRecords(): array;
    public function getCompletedRecords(): array;
    public function verifyRecord(string $accessionId, string $notes, string $scientist): array;
    public function rejectRecord(string $accessionId, string $notes, string $scientist): array;
    public function recheckRecord(string $accessionId, string $notes): array;

    // Labs
    public function getLabs(): array;
    public function createLab(string $name, ?string $ahfoz): array;
    public function deleteLab(string $name): array;

    // Users
    public function getUsers(): array;
    public function createUser(string $name, string $role, ?string $lab, string $password): array;
    public function deleteUser(string $userId): array;
}
```

---

## Database schema

**`labs`**
```sql
CREATE TABLE labs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL UNIQUE,
    ahfoz      VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**`users`**
```sql
CREATE TABLE users (
    id            VARCHAR(50)  PRIMARY KEY,
    name          VARCHAR(255) NOT NULL UNIQUE,
    role          ENUM('Administrator','Lab Scientist','LIS Manager') NOT NULL,
    lab_name      VARCHAR(255) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**`records`**
```sql
CREATE TABLE records (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    accession_id         VARCHAR(50)  NOT NULL UNIQUE,
    patient_name         VARCHAR(255) NOT NULL,
    dob                  DATE         NOT NULL,
    test_type            VARCHAR(255) NOT NULL,
    lab_name             VARCHAR(255) NOT NULL,
    status               VARCHAR(50)  NOT NULL DEFAULT 'Pending Review',
    date_time            DATETIME     NOT NULL,
    ordering_physician   VARCHAR(255) DEFAULT NULL,
    submitted_by         VARCHAR(255) DEFAULT NULL,
    scientist_notes      TEXT         DEFAULT NULL,
    authorized_scientist VARCHAR(255) DEFAULT NULL,
    authorized_time      VARCHAR(50)  DEFAULT NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**`record_parameters`**
```sql
CREATE TABLE record_parameters (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    record_id       INT          NOT NULL,
    name            VARCHAR(255) NOT NULL,
    result          VARCHAR(100) NOT NULL,
    reference_range VARCHAR(100) NOT NULL,
    flag            VARCHAR(50)  NOT NULL DEFAULT 'Normal',
    FOREIGN KEY (record_id) REFERENCES records(id) ON DELETE CASCADE
);
```

---

## Implementation steps

### Step 1 — Environment config

`.env.example` (commit this):
```
API_DRIVER=database

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=lis_gateway
DB_USER=your_db_user
DB_PASS=your_db_password

# Only needed when API_DRIVER=external
EXTERNAL_API_URL=https://api.lis-provider.co.zw/v1
EXTERNAL_API_KEY=your_secret_key
```

`api/config.php` reads `.env` via `parse_ini_file` and returns the values as constants or
a config array. Add `.env` to `.gitignore`.

---

### Step 2 — `ApiContract` interface (`api/contracts/ApiContract.php`)

Define the interface as shown above. This is the single document that describes
what any driver — database or external — must be able to do.

---

### Step 3 — `DatabaseDriver` (`api/drivers/DatabaseDriver.php`)

Implements `ApiContract` using PDO. Internal logic mirrors what the old mock methods
did against `$_SESSION`, but against the database instead.

Key details:
- `getPendingRecords()` / `getCompletedRecords()` JOIN `record_parameters` and assemble
  a nested `parameters` array per record so the return shape matches what `api.js` already
  expects — no frontend changes needed.
- `createUser()` hashes passwords with `password_hash($password, PASSWORD_BCRYPT)`.
- `deleteUser()` and `deleteLab()` enforce the same business rules as the old mock
  (can't delete a lab with active users; can't delete yourself).
- Never return `password_hash` in `getUsers()` output.

---

### Step 4 — `ExternalDriver` (`api/drivers/ExternalDriver.php`)

Implements `ApiContract` using cURL — essentially the production transport that already
exists in the current `ApiClient.php::request()` method, promoted to a full driver.

Each method maps to the expected external API endpoint:

| Method | HTTP call |
|---|---|
| `getPendingRecords()` | `GET /records/pending` |
| `getCompletedRecords()` | `GET /records/completed` |
| `verifyRecord(...)` | `POST /records/{id}/verify` |
| `rejectRecord(...)` | `POST /records/{id}/reject` |
| `recheckRecord(...)` | `POST /records/{id}/recheck` |
| `getLabs()` | `GET /labs` |
| `createLab(...)` | `POST /labs` |
| `deleteLab(...)` | `DELETE /labs/{name}` |
| `getUsers()` | `GET /users` |
| `createUser(...)` | `POST /users` |
| `deleteUser(...)` | `DELETE /users/{id}` |

If the external API's response shape differs from the internal one, adapt it here inside
`ExternalDriver` — the contract's return shape is the source of truth, not the external API's.

---

### Step 5 — `ApiClient` factory (`api/ApiClient.php`)

```php
class ApiClient
{
    public static function make(): ApiContract
    {
        $driver = getenv('API_DRIVER') ?: 'database';

        return match($driver) {
            'external' => new ExternalDriver(),
            default    => new DatabaseDriver(),
        };
    }
}
```

This is the only place the driver choice lives in PHP.

---

### Step 6 — PDO singleton (`api/Database.php`)

Used only by `DatabaseDriver`. Singleton returning a configured PDO instance:
- `ERRMODE_EXCEPTION`
- `FETCH_ASSOC`
- charset utf8mb4

---

### Step 7 — Auth guard (`api/Auth.php`)

```php
Auth::requireLogin();  // emits 401 JSON and exits if no session user
Auth::currentUser();   // returns $_SESSION['user']
```

Used by both `api/index.php` and `index.php`.

---

### Step 8 — Router (`api/index.php`)

Drop-in replacement for root `api.php`. Same URL contract:
- `GET  api/index.php?resource=pending|completed|labs|users`
- `POST api/index.php?action=create_lab|delete_lab|create_user|delete_user|verify_record|reject_record|recheck_record`

```php
$api = ApiClient::make();
// dispatch to $api->getPendingRecords(), $api->createLab(...), etc.
```

No driver-specific code here. Error handling wraps all calls in `try/catch Throwable`.

---

### Step 9 — Update `api.js`

Change every `fetch('api.php?...')` to `fetch('api/index.php?...')`. This is the only
frontend change required.

---

### Step 10 — Update `index.php` form-POST handler

Replace all direct `$_SESSION` writes with driver calls:

| `$_POST['action']` | Replace with |
|---|---|
| `create_lab` | `$api->createLab(...)` |
| `delete_lab` | `$api->deleteLab(...)` |
| `create_user` | `$api->createUser(...)` |
| `delete_user` | `$api->deleteUser(...)` |

Login stays session-based but authenticates against the DB (or the external API if the
driver supports it): fetch the user by name/id, verify with `password_verify()`.

---

### Step 11 — Update `config.php`

Remove the `$_SESSION` seed blocks for `client_labs`, `scientists`, and `records`.
Keep only `session_start()`. Delete `getLabSpecialistsCount()` — that logic now lives
inside the driver.

---

### Step 12 — Database seed (`db/seed.sql`)

Insert the current mock data:
- 4 default labs
- 2 lab scientists + 1 admin — embed bcrypt hashes, never plain-text passwords
- 3 pending records + 2 completed records with their parameters

---

### Step 13 — Cleanup

Delete:
- `api.php` (root)
- `ApiClient.php` (root)
- `data.php`

Update `CLAUDE.md` to reflect the new structure.

---

## How to adopt an external API later

1. Add `EXTERNAL_API_URL` and `EXTERNAL_API_KEY` to `.env`
2. Change `API_DRIVER=database` → `API_DRIVER=external`
3. Done — no PHP code changes required

If the external API's response format differs from the internal contract, all adaptation
happens inside `ExternalDriver` only.

---

## Assumptions and decisions

- **MySQL/MariaDB** is the target. Switching to PostgreSQL or SQLite requires only changing
  the DSN in `Database.php`.
- `lab_name` is a plain string in `users` and `records` (not an integer FK) to match the
  current data shape. A proper FK can be added later.
- Passwords use bcrypt. Plain-text values from `config.php` must never appear in seed SQL.
- `$_SESSION['user']` shape is unchanged; login writes to session and the DB is not
  queried on every request.
- No Composer packages or frameworks — plain PHP, PDO, and cURL only.
