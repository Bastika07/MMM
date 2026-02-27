# MMM - MultiMadness Management

A legacy PHP-based LAN-party management system powering [multimadness.de](https://www.multimadness.de) and related event sites (northcon.de, lanresort.de, dimension6.de, …).

---

## Table of Contents

- [Overview](#overview)
- [Directory Structure](#directory-structure)
- [Key Files](#key-files)
- [Architecture](#architecture)
- [Dependencies](#dependencies)
- [Configuration & Environment](#configuration--environment)
- [Known Issues & Security Notes](#known-issues--security-notes)
- [Recommended Improvements](#recommended-improvements)

---

## Overview

MMM (PELAS - Party Event & LAN Administration System) handles:

- **User registration & login** - session management, cookie-based auth
- **Ticketing / accounting** - ticket purchase, invoice generation, PayPal & wire-transfer payment tracking
- **Seating plan** - interactive seat reservation (v1 and v2)
- **Tournament system** - bracket management, team creation, rankings, match details
- **Forum & news** - thread/post system with board types (forum, news, tournament comments)
- **Archive** - photos, videos, newspaper articles from past events
- **Admin panel** - 80+ backend scripts for managing all of the above
- **Mailing / newsletter** - GDPR-aware opt-in mailing via PHPMailer
- **Misc** - geek-radar (attendee map), clan management, poll/survey, guest-server registration, barcode generation, PDF/QR generation

The system serves multiple event brands from a single codebase via a `MANDANTID` constant and hostname-based configuration in `constants.php`.

---

## Directory Structure

```
/
├── README.md
├── includes/                   # Core libraries and shared PHP code
│   ├── constants.php           # All configuration, DB credentials, constants
│   ├── dblib.php               # MySQLi database wrapper (DB:: static class)
│   ├── session.php             # Session creation / DB persistence
│   ├── getsession.php          # Session lookup; sets $nLoginID / $sLogin globals
│   ├── security.php            # Login guard (redirects to login.php if not authenticated)
│   ├── format.php              # Text formatting helpers (BBCode, emoji, etc.)
│   ├── language.inc.php        # Language / i18n include
│   ├── german.inc.php          # German string translations
│   ├── english.inc.php         # English string translations
│   ├── pelasfunctions.php      # Core business logic (invoices, tickets, accounting)
│   ├── postfunctions.php       # Forum post helpers
│   ├── checkrights.php         # Right/permission checks
│   ├── util.php                # General utility functions
│   ├── upload.php              # File upload helpers
│   ├── json.php                # JSON helpers
│   ├── sitzlib.php             # Seating-plan library
│   ├── coverage.php            # Attendance/coverage utilities
│   ├── bugtracking.inc.php     # Bug-tracking helpers
│   ├── t_compat.inc.php        # Backwards-compatibility shims
│   ├── hostconfig.php          # (removed - emits die())
│   ├── PHPMailer/              # PHPMailer library for outgoing email
│   ├── smarty/                 # Smarty template engine (SmartyBC)
│   ├── classes/                # Domain model classes (Board, Thread, User, …)
│   ├── turnier/                # Tournament subsystem (8 classes)
│   ├── pelasfront/             # Frontend-facing page-logic modules
│   ├── multimadness/           # Brand-specific overrides / assets
│   ├── html2pdf/               # HTML-to-PDF conversion library
│   ├── fonts/                  # Fonts used by PDF generation
│   └── profiler.inc/           # Profiling helpers
│
└── multimadness.de/            # Web root for multimadness.de
    ├── index.php               # Front controller / router
    ├── admin/                  # Admin panel (80+ PHP scripts)
    ├── page/                   # Frontend page modules (30+ files)
    ├── pelasfront/             # Symlink/copy of frontend modules
    ├── css/                    # Stylesheets
    ├── js/                     # JavaScript (jQuery 2.1.1, lightbox, html2canvas)
    ├── fonts/                  # Web fonts
    ├── img/                    # Images (logos, banners)
    ├── gfx/                    # Graphics / smileys
    ├── gfx_turnier/            # Tournament-specific graphics
    ├── forumicons/             # Forum icon assets
    ├── geekradar/              # Geek-radar (attendee location map) assets
    ├── html2pdf/               # PDF generation front-end
    └── .htaccess               # Apache URL / access rules
```

---

## Key Files

| File | Purpose |
|---|---|
| `multimadness.de/index.php` | Front controller: maps `?page=N` to `page/<name>.php` modules; handles cookie consent and newsletter popup |
| `includes/constants.php` | All runtime configuration: DB credentials, mail credentials, file paths, status codes, category IDs, constants |
| `includes/dblib.php` | `DB::` static class wrapping MySQLi; provides `DB::connect()`, `DB::query()`, `DB::getOne()`, `DB::getAll()` etc.; also `safe()` escape helper and `BenutzerHatRecht()` permission check |
| `includes/getsession.php` | Reads session cookie, queries `SESSION` table, populates `$nLoginID` / `$sLogin` / `$loginID` globals |
| `includes/security.php` | Guards admin/protected pages; redirects to `login.php` if `$nLoginID` is not set |
| `includes/pelasfunctions.php` | Business logic: invoice creation, ticket status transitions, accounting helpers |
| `includes/turnier/` | Tournament engine: `Turnier`, `Team`, `Match`, `Ranking`, `TurnierSystem` classes |
| `multimadness.de/admin/index.php` | Admin panel entry point; contains legacy `mysql_*` calls (non-functional on PHP ≥ 7) |

---

## Architecture

### Request Flow

```
Browser → multimadness.de/index.php
            ├── includes/getsession.php  (auth state)
            ├── page/<name>.top.php      (optional early output)
            ├── page/<name>.head.php     (optional <head> additions)
            └── page/<name>.php         (page body)
                  └── includes/pelasfront/<module>.php  (shared logic)
```

### Database

- **Engine**: MySQL / MariaDB accessed via MySQLi (`DB::` class in `dblib.php`)
- Connection parameters are set per-hostname in `constants.php`
- Notable tables: `USER`, `SESSION`, `RECHTZUORDNUNG`, `ACCOUNTING`, `SITZPLAN`, `TURNIER`, `TEAM`, `MATCH`, `BOARD`, `THREAD`, `POST`, `ARCHIV`, `MANDANT`

### Templating

- Smarty 3 (SmartyBC compatibility class) for some admin views
- Most frontend pages use plain PHP with `echo` / inline HTML

### Routing

`index.php` maps integer page IDs to file names:

| Page ID | Module |
|---|---|
| 1 | start (home) |
| 2 | news |
| 5 | login / logout |
| 6 | accounting (ticketing) |
| 8 | teilnehmerliste (attendee list) |
| 9/13 | sitzplan (seating) |
| 10/12 | forum |
| 14 | archiv (archive) |
| 20-30 | tournament sub-pages |
| 40-47 | static info pages |

---

## Dependencies

All dependencies are vendored (no package manager):

| Dependency | Location | Purpose |
|---|---|---|
| PHPMailer | `includes/PHPMailer/` | Outgoing email |
| Smarty 3 (SmartyBC) | `includes/smarty/` | Templating |
| jQuery 2.1.1 | `multimadness.de/js/` | Frontend JS |
| Lightbox | `multimadness.de/js/`, `css/` | Image gallery |
| html2canvas | `multimadness.de/` | Client-side canvas screenshot |
| html2pdf (TCPDF-based) | `includes/html2pdf/`, `multimadness.de/html2pdf/` | PDF generation |
| phpqrcode | `includes/phpqrcode.php` | QR code generation |
| class.ezpdf / class.pdf | `includes/` | Legacy PDF generation |
| fpdf | `includes/fpdf.php` | PDF generation |

---

## Configuration & Environment

All runtime configuration lives in **`includes/constants.php`**.

The active configuration block is selected by matching `$_SERVER['SERVER_NAME']` to a known hostname. Three environments are defined:

| `$srv_conf` value | When used |
|---|---|
| `urtyp_live_internet` | Production (multimadness.de, northcon.de, …) |
| `urtyp_dev_internet` | Internet-facing development (*.innovalan.de dev hosts) |
| `urtyp_dev_intranet` | LAN-party intranet (*.innovalan.de LAN hosts) |
| `madnix_live` | LAN-party live intranet (www.lan.multimadness.de) |

Each block sets:
- `PELASHOST` / `PELASDIR` / `BASE_URL` - URL and filesystem paths
- `LOCATION` - `'internet'` or `'intranet'` (controls menu visibility)
- `$dbname`, `$dbhost`, `$dbuser`, `$dbpass` - database connection

**The `MANDANTID` (tenant/brand ID) is hard-coded in the frontend `index.php`** (`$nPartyID = 2`).

### Defined Constants (selection)

| Constant | Purpose |
|---|---|
| `ACCOUNTING` | `'NEW'` enables the new accounting subsystem |
| `BINGMAPS_KEY` | Bing Maps API key used for the attendee location map |
| `MAIL_*` | SMTP credentials for transactional mail |
| `MAIL_*_NEWSLETTER` | SMTP credentials for newsletter mail |
| `ACC_STATUS_*` | Accounting/ticket status codes |
| `BOARD_*` / `BT_*` | Forum board flags and types |
| `DESIGN_*` | Forum board display modes |
| `USER_ONLINE_TIMEOUT` | Seconds of inactivity before user is shown as offline (300 s) |

---

## Known Issues & Security Notes

> ⚠️ **This section documents pre-existing issues found during analysis. They are not introduced by this repository's maintainers intentionally.**

### 🔴 Critical

1. **Credentials committed to source control** - `includes/constants.php` contains production database passwords, mail passwords, and a Bing Maps API key in plain text. These should be moved to environment variables or an `.env` file that is excluded from version control.

2. **Deprecated `mysql_*` functions** - `multimadness.de/admin/index.php` still uses the removed `mysql_query()` / `mysql_fetch_assoc()` API (removed in PHP 7.0). These calls are non-functional on any modern PHP version. The rest of the codebase correctly uses the `DB::` MySQLi wrapper.

3. **SQL injection** - Several files build SQL queries by string-interpolating unescaped variables directly (e.g. `includes/format.php`, `includes/pelasfront/casemod.php`, `includes/pelasfront/archiv.php`, `includes/turnier/Team.class.php`). The `safe()` / `DB::$link->real_escape_string()` helper exists but is not consistently applied. Prepared statements (MySQLi or PDO) should be used throughout.

### 🟠 High

4. **No CSRF protection** - Forms (newsletter opt-in, login, seating reservation, accounting) do not include or validate a CSRF token.

5. **Hard-coded absolute filesystem paths** - `constants.php` and several includes reference `/var/www/vhosts/hosting103794.af995.netcup.net/…` directly, making the codebase non-portable without editing PHP files.

6. **`hostconfig.php` calls `die()`** - `includes/hostconfig.php` was removed and replaced with a `die()` stub. Any code that still requires it will produce a fatal error.

### 🟡 Medium

7. **Error suppression with `@`** - Many database calls use the `@` operator to silence errors instead of proper exception handling, making debugging very difficult.

8. **Global variable pollution** - Auth state is propagated via bare globals (`$loginID`, `$nLoginID`, `$sLogin`) included from `getsession.php`, with no encapsulation.

9. **Inline HTML / mixed concerns** - Pages mix HTML, SQL, and business logic in the same file with no separation, making testing and maintenance harder.

10. **Outdated frontend libraries** - jQuery 2.1.1 (EOL) and other vendored JS/CSS libraries are not receiving security updates.

### 🟢 Low / Informational

11. **No automated tests** - There is no test suite (no PHPUnit, no Jest, no CI pipeline). All validation is manual.

12. **Commented-out dead code** - Large blocks of commented-out SQL and PHP exist throughout `pelasfunctions.php` and `index.php`.

13. **Encoding issues** - Some comments in `constants.php` show garbled UTF-8 (`Ã`, `ü` rendered as multi-byte artefacts), suggesting the file was edited with incorrect encoding at some point.

---

## Recommended Improvements

| Priority | Action |
|---|---|
| 🔴 | Move all credentials/secrets to `.env` / environment variables; add `.env` to `.gitignore` |
| 🔴 | Replace legacy `mysql_*` calls in `admin/index.php` with MySQLi / `DB::` wrapper |
| 🔴 | Use prepared statements (MySQLi `prepare()` / `bind_param()`) for all DB queries |
| 🟠 | Add CSRF token generation and validation to all state-changing forms |
| 🟠 | Replace hard-coded paths with a single `BASE_DIR` constant derived at runtime |
| 🟡 | Add PHPUnit test coverage for core business logic (`pelasfunctions.php`, `DB::`, tournament classes) |
| 🟡 | Introduce a lightweight router/framework to separate routing, controllers, and views |
| 🟡 | Update or replace vendored frontend libraries (jQuery, lightbox) |
| 🟢 | Add a `.github/workflows/` CI pipeline (PHP lint + static analysis with PHPStan/Psalm) |
| 🟢 | Add a `docker-compose.yml` for reproducible local development |
| 🟢 | Clean up dead/commented-out code |
