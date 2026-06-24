# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running the app

No build step — serve directly with PHP's built-in server:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/index.php`. There is no Composer, no npm, and no test suite.

Default credentials (defined in `config.php`):
- Admin: `admin-01` / `admin123`
- Scientist: `S. Sibanda` / `password123`

## Architecture

This is a multi-tenant Laboratory Information System (LIS) authorization portal for Zimbabwe. All state lives in `$_SESSION` — there is no database.

### Request flow

There are two parallel entry points that share the same session:

1. **Traditional form-POST MVC** (`index.php`): handles login, logout, and admin CRUD via `$_POST`. Includes partial PHP views from `views/` and assembles the full page layout with `header.php` and `sidebar.php`. Also contains the SPA navigation layer (a `fetch`-based JS listener that swaps `<main>` content without full-page reloads).

2. **JSON API gateway** (`api.php`): the endpoint that the browser's JavaScript calls via `api.js`. Validates the session, then delegates every read and mutation to `ApiClient.php`. Returns JSON only.

### ApiClient.php — BFF with mock mode

`ApiClient` is the Backend-for-Frontend layer. The `USE_MOCK` constant (top of the class) controls whether calls go to `$_SESSION` (mock, current default) or are forwarded to a real LIS API via cURL.

To switch to a real API:
1. Set `USE_MOCK = false`
2. Set `API_BASE_URL` to the real endpoint
3. Set `API_KEY` to the bearer token

The browser never sees the API key — `api.php` is the only caller of `ApiClient`.

### Session data keys

| Key | Contents |
|---|---|
| `$_SESSION['user']` | Logged-in user object (`id`, `name`, `role`, `lab`) |
| `$_SESSION['client_labs']` | Registered facility laboratories |
| `$_SESSION['scientists']` | All user accounts |
| `$_SESSION['pending_records']` | Records awaiting authorization |
| `$_SESSION['completed_records']` | Approved / rejected records |

### Pages and roles

| URL | View | Access |
|---|---|---|
| `?page=pending` | Authorization queue | All logged-in users |
| `?page=completed` | Released reports | All logged-in users |
| `?page=admin` | System management (labs + users) | `Administrator` or `LIS Manager` role only |

### API surface (`api.php`)

- `GET ?resource=pending|completed|labs|users`
- `POST ?action=create_lab|delete_lab|create_user|delete_user|verify_record|reject_record|recheck_record`

The JavaScript client in `api.js` wraps every call and throws on `data.error`.

### Stale file

`data.php` is a leftover static array file that is no longer included anywhere. It has been superseded by `config.php`, which seeds the same data into `$_SESSION` on first request.
