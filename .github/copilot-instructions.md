# Copilot Instructions for MMM (MultiMadness Management)

## Project Overview

MMM (also called PELAS – Party Event & LAN Administration System) is a legacy PHP-based LAN-party management system powering [multimadness.de](https://www.multimadness.de) and related event sites (northcon.de, lanresort.de, dimension6.de, …).

It handles user registration/login, ticketing/accounting, seating plans, tournament management, forums/news, archive (photos/videos), an admin panel, mailing/newsletter, and miscellaneous features like a geek-radar and clan management.

The system serves multiple event brands from a single codebase via a `MANDANTID` constant and hostname-based configuration in `includes/constants.php`.

---

## Tech Stack

- **Language**: PHP 8.x (legacy codebase, no Composer, no autoloader)
- **Database**: MySQL / MariaDB via MySQLi (`DB::` static class in `includes/dblib.php`)
- **Templating**: Smarty 3 (SmartyBC) for some admin views; plain PHP `echo`/inline HTML for most pages
- **Frontend**: jQuery 2.1.1 (**outdated/EOL — has known security vulnerabilities; do not rely on it for new code**), plain HTML/CSS; vendored JS/CSS libs
- **PDF**: FPDF, html2pdf (TCPDF-based), class.ezpdf
- **Email**: PHPMailer (`includes/PHPMailer/`)
- **Dependencies**: All vendored under `includes/` — no package manager

---

## Repository Structure

```
/
├── includes/           # Core libraries and shared PHP code (business logic, DB, session, etc.)
│   ├── constants.php   # All configuration, DB credentials, constants (reads from .env)
│   ├── dblib.php       # DB:: static class wrapping MySQLi
│   ├── pelasfunctions.php  # Core business logic (invoices, tickets, accounting)
│   ├── pelasfront/     # Frontend page-logic modules (60+ files)
│   ├── turnier/        # Tournament engine (16 classes)
│   ├── classes/        # Domain model classes (Board, Thread, Post, User2BeamerMessage, Smarty wrappers)
│   └── ...             # Other helpers, libraries, and admin modules
└── multimadness.de/    # Web root
    ├── index.php       # Front controller: maps ?page=N to page/<name>.php
    ├── admin/          # Admin panel (80+ PHP scripts)
    └── page/           # Frontend page modules (40+ files)
```

---

## Local Development

1. Use [XAMPP](https://www.apachefriends.org/) with PHP 8.x and MySQL/MariaDB.
2. Copy `multimadness.de/` into `htdocs/` (or configure a virtual host).
3. Ensure `includes/` is accessible one level above the web root.
4. Create `includes/.env` with DB/mail credentials (see README for full variable list).
5. Import a SQL dump, start Apache + MySQL, open `http://localhost/`.

---

## Environment & Configuration

- All runtime config lives in `includes/constants.php`.
- Credentials are loaded from `includes/.env` (not committed — listed in `.gitignore`).
- The active config block is selected by matching `$_SERVER['SERVER_NAME']`.
- **Never hard-code credentials** — always use `getenv()` / environment variables.

---

## Database Access

Always use the `DB::` static class defined in `includes/dblib.php`:

```php
// Prepared statement (preferred — prevents SQL injection)
$result = DB::query("SELECT * FROM USER WHERE nUserID = ?", $userId);

// Fetch helpers
$row  = DB::getOne("SELECT ...");
$rows = DB::getAll("SELECT ...");
```

**Never** interpolate `$_GET`/`$_POST`/`$_COOKIE` values directly into SQL strings. Use `?` placeholders with `DB::query()`.

```php
// ❌ WRONG — SQL injection risk
$result = DB::query("SELECT * FROM USER WHERE nUserID = " . $_GET['id']);

// ✅ CORRECT — use prepared statement placeholders
$result = DB::query("SELECT * FROM USER WHERE nUserID = ?", $_GET['id']);
```

---

## Authentication & Session

- Session state is populated by `includes/getsession.php` → sets `$nLoginID`, `$sLogin`, `$loginID` globals.
- Protected pages `require_once` `includes/security.php`, which redirects to `login.php` if the user is not authenticated.
- Permission checks use `BenutzerHatRecht()` (in `includes/dblib.php`) and helpers in `includes/checkrights.php`.

---

## Coding Conventions

- **PHP style**: no strict typing enforced; follow the surrounding file's style.
- **SQL**: always use prepared statements via `DB::query($sql, ...$params)`.
- **No new credentials** in source files — use `getenv()`.
- **No new `mysql_*` calls** — use `DB::` methods only.
- **Error suppression** (`@`) is a known issue in the codebase; avoid adding more.
- Keep changes minimal and surgical; the codebase is large and tightly coupled.

---

## Known Limitations (do not introduce more)

- No CSRF protection on forms (existing issue — not yet fixed).
- No automated test suite (no PHPUnit, no Jest).
- Some hard-coded absolute filesystem paths remain in dev/intranet config blocks.
- Frontend libraries (jQuery 2.1.1) are outdated.

---

## Security Guidelines

- All DB queries with user input **must** use prepared statements.
- Secrets belong in `includes/.env`, read via `getenv()`.
- Do not introduce new SQL injection vectors, XSS sinks, or hard-coded credentials.
