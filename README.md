# MMM - MultiMadness Management

A legacy PHP-based LAN-party management system powering [multimadness.de](https://www.multimadness.de) and related event sites (northcon.de, lanresort.de, dimension6.de, …).

---

## Table of Contents

- [Overview](#overview)
- [Supported Brands / Hostnames](#supported-brands--hostnames)
- [Local Development](#local-development)
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

## Supported Brands / Hostnames

The `includes/constants.php` hostname switch recognises the following production domains (all map to `urtyp_live_internet`):

| Brand | Hostnames |
|---|---|
| MultiMadness | `multimadness.de`, `www.multimadness.de`, `madness4ever.de` |
| NorthCon | `northcon.de`, `www.northcon.de`, `inet.northcon.de`, `admin.northcon.de` |
| LAN Resort | `lanresort.de`, `www.lanresort.de`, `inet.lanresort.de` |
| The Summit | `the-summit.de`, `www.the-summit.de`, `thesummit.de`, `inet.the-summit.de` |
| The Activation | `the-activation.de`, `theactivation.de`, `www.the-activation.de` |
| Dimension 6 | `dimension6.de`, `www.dimension6.de`, `d6-lan.de`, `ildm6.de` |
| LAN Fortress | `lanfortress.de`, `www.lanfortress.de` |
| eSport Arena | `esportarena.tv`, `esportarena.de` |
| InnovaLAN (admin) | `admin.innovalan.de`, `pelas.innovalan.de`, `friends.innovalan.de` |

Dev (internet) variants follow the pattern `<brand>-dev.innovalan.de`; intranet variants use `<brand>-lan.innovalan.de`. The live intranet during the party is served from `www.lan.multimadness.de` (`madnix_live`).

---

## Local Development

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine + Compose plugin)

### Quick Start

```bash
# 1. Copy the environment template and fill in values (or keep the defaults for local testing)
cp includes/.env.example includes/.env

# 2. Start all services (web + MariaDB + phpMyAdmin + Mailhog)
docker compose up -d
```

| Service | URL |
|---|---|
| Web application | <http://localhost:8080> |
| phpMyAdmin | <http://localhost:8081> |
| Mailhog (SMTP UI) | <http://localhost:8025> |

The `includes/` directory is mounted read-only into the container at `/var/www/includes`; `multimadness.de/` becomes the Apache document root at `/var/www/html`.

Database credentials and mail settings are injected via environment variables defined in `includes/.env` (see `docker-compose.yml` for the full list). Environment variables override any hard-coded values inside `constants.php`.

> **Note** – a blank database is created by default. Import a recent SQL dump to get a working dataset.

---

## Directory Structure

```
/
├── README.md
├── Dockerfile                  # PHP 8.2 + Apache image for local development
├── docker-compose.yml          # Local dev stack (web, MariaDB, phpMyAdmin, Mailhog)
├── includes/                   # Core libraries and shared PHP code
│   ├── .env.example            # Environment variable template (copy to .env)
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
│   ├── sitzplan_generate.php   # Seating-plan SVG/HTML generator
│   ├── coverage.php            # Attendance/coverage utilities
│   ├── mailing_execute.php     # Newsletter/mailing execution helper
│   ├── bugtracking.inc.php     # Bug-tracking helpers
│   ├── t_compat.inc.php        # Backwards-compatibility shims
│   ├── hostconfig.php          # (removed – emits die())
│   ├── barcode.php             # Barcode generation
│   ├── phpqrcode.php           # QR code generation
│   ├── fpdf.php                # FPDF PDF library
│   ├── class.ezpdf.php         # Legacy ezPDF library
│   ├── class.pdf.php           # Legacy PDF helper
│   ├── PHPMailer/              # PHPMailer library for outgoing email
│   ├── smarty/                 # Smarty template engine (SmartyBC)
│   ├── classes/                # Domain model classes
│   │   ├── Board.class.php
│   │   ├── Thread.class.php
│   │   ├── Post.class.php
│   │   ├── User2BeamerMessage.class.php
│   │   └── PelasSmarty / SmartyAdmin / SmartyForum / SmartyBugTrack (Smarty wrappers)
│   ├── turnier/                # Tournament engine (16 classes + constants)
│   │   ├── Turnier.class.php
│   │   ├── TurnierSystem.class.php
│   │   ├── TurnierAdmin.class.php
│   │   ├── TurnierGroup.class.php
│   │   ├── TurnierLiga.class.php
│   │   ├── TurnierRanking.class.php
│   │   ├── TurnierCoverage.class.php
│   │   ├── TurnierPreis.class.php
│   │   ├── TurnierExportNGL.class.php
│   │   ├── TurnierExportWWCL.class.php
│   │   ├── Team.class.php
│   │   ├── TeamSystem.class.php
│   │   ├── Match.class.php
│   │   ├── Round.class.php
│   │   ├── Jump.class.php
│   │   ├── Tree.php
│   │   └── t_constants.php
│   ├── turnier-frontend/       # Admin-side tournament management pages
│   ├── pelasfront/             # Frontend-facing page-logic modules (60+ files)
│   ├── admin/                  # Shared admin helper modules
│   ├── multimadness/           # Brand-specific overrides / assets
│   ├── html2pdf/               # HTML-to-PDF conversion library
│   ├── fonts/                  # Fonts used by PDF generation
│   ├── xmlrpc/                 # XML-RPC helpers (unused/legacy)
│   └── profiler.inc/           # Profiling helpers
│
└── multimadness.de/            # Web root for multimadness.de
    ├── index.php               # Front controller / router
    ├── admin/                  # Admin panel (80+ PHP scripts)
    │   └── turnier/            # Tournament admin scripts
    ├── page/                   # Frontend page modules (40+ files)
    │   └── turnier/            # Tournament frontend pages
    ├── css/                    # Stylesheets
    ├── js/                     # JavaScript (jQuery 2.1.1, lightbox, html2canvas)
    ├── fonts/                  # Web fonts
    ├── img/                    # Images (logos, banners)
    ├── images/                 # Additional images
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
| `includes/constants.php` | All runtime configuration: DB credentials, mail credentials, file paths, status codes, category IDs, constants; includes minimal `.env` loader |
| `includes/dblib.php` | `DB::` static class wrapping MySQLi; provides `DB::connect()`, `DB::query()`, `DB::getOne()`, `DB::getAll()` etc.; also `safe()` escape helper and `BenutzerHatRecht()` permission check |
| `includes/getsession.php` | Reads session cookie, queries `SESSION` table, populates `$nLoginID` / `$sLogin` / `$loginID` globals |
| `includes/security.php` | Guards admin/protected pages; redirects to `login.php` if `$nLoginID` is not set |
| `includes/pelasfunctions.php` | Business logic: invoice creation, ticket status transitions, accounting helpers, PayPal fee calculation, password hashing |
| `includes/sitzlib.php` | Seating-plan library: seat lookup, reservation, block management |
| `includes/turnier/` | Tournament engine: `Turnier`, `Team`, `Match`, `Round`, `Jump`, `Ranking`, `TurnierSystem`, group and liga sub-classes |
| `includes/turnier-frontend/` | Admin-facing tournament management pages (tap, verwaltung, seeding, transfer, export, prices, admins) |
| `includes/pelasfront/` | Frontend page-logic modules shared between web roots (accounting, archiv, forum, geekradar, sitzplan, login, …) |
| `multimadness.de/admin/index.php` | Admin panel entry point; contains legacy `mysql_*` calls (non-functional on PHP ≥ 7) |
| `multimadness.de/admin/controller.php` | Admin AJAX/action dispatcher |
| `Dockerfile` | PHP 8.2 + Apache image with `mysqli` extension and `mod_rewrite` enabled |
| `docker-compose.yml` | Local dev stack: web, MariaDB 10.11, phpMyAdmin, Mailhog |
| `includes/.env.example` | Environment variable template; copy to `includes/.env` before first run |

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
| 1 / 111 | start / start2 (home – `111` is an alternate home variant) |
| 2 | news |
| 3 | info |
| 4 | benutzerdetails (user profile) |
| 5 | login / logout |
| 6 | accounting (ticketing) |
| 8 | teilnehmerliste (attendee list) |
| 9 / 13 | sitzplan (seating v1) |
| 10 / 12 | forum |
| 11 | login_edit (change password/profile) |
| 14 | archiv (archive) |
| 15 | archiv_upload |
| 16 | geekradar (attendee map) |
| 17 | kontaktformular (contact form) |
| 18 | clanverwaltung (clan management) |
| 19 | clandetails |
| 20–30 | tournament sub-pages (list, detail, FAQ, ranking, table, tree, match, team create/detail/swap) |
| 31 | gastserver (guest server registration) |
| 32 | umfrage (poll/survey) |
| 40 | lokation |
| 41 | netzwerk |
| 42 | bedingungen (T&Cs) |
| 43 | impressum |
| 44 | team |
| 45 | verpflegung (catering) |
| 46 | umgebungskarte (area map) |
| 47 | datenschutz (privacy policy) |
| 48 | sponsoren |
| 49 | shirtshop |
| 99 | sitzplanv2 (seating v2) |
| 500 | covid19 |
| 999 | error |

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

### `.env` support

`constants.php` includes a minimal `.env` file loader at the top. On startup it reads `includes/.env` (if present), registering each `KEY=VALUE` pair via `putenv()`. Environment variables already set in the process (e.g. by Docker) take precedence over the `.env` file.

Copy `includes/.env.example` → `includes/.env` and fill in your values before first run. **Never commit `includes/.env` to version control** (it is already in `.gitignore`).

### Environment selection

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

### Environment variables (full list)

| Variable | Used by | Purpose |
|---|---|---|
| `LIVE_DB_HOST` / `LIVE_DB_NAME` / `LIVE_DB_USER` / `LIVE_DB_PASS` | `urtyp_live_internet` | Production DB connection |
| `DEV_DB_HOST` / `DEV_DB_NAME` / `DEV_DB_USER` / `DEV_DB_PASS` | `urtyp_dev_internet` and `urtyp_dev_intranet` | Internet-facing dev + intranet dev DB connection |
| `LAN_DB_HOST` / `LAN_DB_NAME` / `LAN_DB_USER` / `LAN_DB_PASS` | `madnix_live` (party intranet) | Live party intranet DB connection |
| `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` | `constants.php` | Transactional SMTP |
| `MAIL_HOST_NEWSLETTER` / `MAIL_USERNAME_NEWSLETTER` / `MAIL_PASSWORD_NEWSLETTER` | `constants.php` | Newsletter SMTP |
| `BINGMAPS_KEY` | `index.php` | Bing Maps API key (geek-radar map) |
| `LIVE_PELASDIR` | `urtyp_live_internet` | Override for the PELAS filesystem root |
| `LIVE_SMARTY_BASE_DIR` | `urtyp_live_internet` | Override for Smarty base directory |

### Defined PHP constants (selection)

| Constant | Purpose |
|---|---|
| `ACCOUNTING` | `'NEW'` enables the new accounting subsystem |
| `BINGMAPS_KEY` | Bing Maps API key used for the attendee location map |
| `MAIL_*` | SMTP credentials for transactional mail |
| `MAIL_*_NEWSLETTER` | SMTP credentials for newsletter mail |
| `ACC_STATUS_OFFEN` / `ACC_STATUS_BEZAHLT` / `ACC_STATUS_STORNIERT` | Accounting/ticket status codes (1 / 2 / 3) |
| `ACC_ZAHLUNGSWEISE_*` | Payment method codes: Überweisung=1, PayPal=2, Bar=3 |
| `BOARD_INLINE` / `BOARD_CLOSED` / `BOARD_HIDDEN` | Forum board flags (bitmask) |
| `BT_FORUM` / `BT_NEWS` / `BT_TURNIERCOMMENTS` | Forum board types |
| `DESIGN_FORUM` / `DESIGN_NEWS` / `DESIGN_NEWSCOMMENTS` / `DESIGN_COMMENTS` / `DESIGN_NEWSADMIN` | Forum board display modes |
| `USER_ONLINE_TIMEOUT` | Seconds of inactivity before user is shown as offline (300 s) |
| `PELASHOST` / `PELASDIR` / `BASE_URL` | URL and filesystem paths (set per hostname) |
| `LOCATION` | `'internet'` or `'intranet'` (controls menu visibility) |
| `SMARTY_HOME_DIR` / `SMARTY_CLASS` | Smarty library path |
| `NEWSBILD_DIR` / `SLIDER_DIR` / `VERPFLEGUNG_DIR` / `LOCATION_DIR` / `SPONSOR_DIR` | Upload directories for image categories |
| `UPLOADDIR` | Temp directory for file uploads (`/tmp/`) |
| `MANDANTID` | Tenant/brand ID (hard-coded `2` in `index.php`) |

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
| 🔴 | Move all credentials/secrets to `.env` / environment variables; `includes/.env.example` now exists — ensure production is using it |
| 🔴 | Replace legacy `mysql_*` calls in `admin/index.php` with MySQLi / `DB::` wrapper |
| 🔴 | Use prepared statements (MySQLi `prepare()` / `bind_param()`) for all DB queries |
| 🟠 | Add CSRF token generation and validation to all state-changing forms |
| 🟠 | Replace hard-coded absolute paths with a single `BASE_DIR` constant derived at runtime |
| 🟡 | Add PHPUnit test coverage for core business logic (`pelasfunctions.php`, `DB::`, tournament classes) |
| 🟡 | Introduce a lightweight router/framework to separate routing, controllers, and views |
| 🟡 | Update or replace vendored frontend libraries (jQuery 2.1.1 is EOL, update to 3.x+) |
| 🟢 | Add a `.github/workflows/` CI pipeline (PHP lint + static analysis with PHPStan/Psalm) |
| 🟢 | Clean up dead/commented-out code in `pelasfunctions.php` and `index.php` |
| 🟢 | Fix UTF-8 encoding artefacts in `constants.php` |
