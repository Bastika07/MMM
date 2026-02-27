# MMM - MultiMadness Management

A legacy PHP-based LAN-party management system powering [multimadness.de](https://www.multimadness.de) and related event sites (northcon.de, lanresort.de, dimension6.de, …).

---

## Table of Contents

- [Overview](#overview)
- [Supported Brands / Hostnames](#supported-brands--hostnames)
- [Local Development](#local-development)
- [Testing](#testing)
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

## Local Development (XAMPP)

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) with PHP 8.x and MySQL/MariaDB

### Quick Start

1. Copy the `multimadness.de/` folder into your XAMPP `htdocs/` (or configure a virtual host pointing at it).
2. Make sure `includes/` is accessible one level above the web root (or adjust the `require_once` paths in `index.php` to match your setup).
3. Fill in your database and mail settings in `includes/.env` (create it if it does not exist – copy the variables listed in the [Environment variables](#environment-variables-full-list) table).
4. Import a SQL dump into your local MySQL/MariaDB instance, then start Apache and MySQL in the XAMPP control panel.
5. Open `http://localhost/` in your browser.

> **Note** – any unrecognised hostname (including `localhost`) falls through to the `urtyp_live_internet` configuration block in `constants.php`, which reads DB credentials from `includes/.env`.

---

## Testing

The project uses [PHPUnit](https://phpunit.de/) for automated tests.  The CI
pipeline (`.github/workflows/phpunit.yml`) runs the full suite against PHP 8.1,
8.2 and 8.3 with a MySQL 8.0 service container.

### Test structure

| Suite | Location | Requires MySQL |
|---|---|---|
| **Unit** | `tests/Unit/` | No (DB connection is still established at bootstrap time) |
| **Integration** | `tests/Integration/` | Yes |

| Test class | Covers |
|---|---|
| `PelasTest` | Pure methods of `PELAS`: `HashPassword`, `PayPalGebuehr`, `formatBestellNr`, `formatTicketNr`, `userLink`, `countdown` |
| `CsrfTest` | `csrf_token()`, `csrf_field()`, `csrf_verify()` helpers in `dblib.php` |
| `TurnierConstantsTest` | Tournament flag/status constants in `includes/turnier/t_constants.php` |
| `DbTest` | `DB::query()`, `DB::getOne()`, `DB::getRow()`, `DB::getRows()`, `DB::getCol()`, `PELAS::logging()` |

### Running locally

**Prerequisites** – PHP 8.x, [Composer](https://getcomposer.org/), MySQL/MariaDB.

```bash
# Install dev dependencies
composer install

# Create a dedicated test database (once)
mysql -u root -p <<'SQL'
CREATE DATABASE IF NOT EXISTS mmm_test;
CREATE USER IF NOT EXISTS 'mmm_test'@'localhost' IDENTIFIED BY 'mmm_test_pass';
GRANT ALL ON mmm_test.* TO 'mmm_test'@'localhost';

CREATE TABLE IF NOT EXISTS mmm_test.logging (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  userID     INT         DEFAULT NULL,
  msg        TEXT        NOT NULL,
  cat        VARCHAR(64) DEFAULT NULL,
  created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
);
SQL

# Run all tests
vendor/bin/phpunit

# Run only the Unit suite (no DB I/O)
vendor/bin/phpunit --testsuite Unit

# Run only the Integration suite
vendor/bin/phpunit --testsuite Integration
```

The bootstrap (`tests/bootstrap.php`) reads the connection from environment
variables; the defaults match the setup above.  Override them as needed:

```bash
TEST_DB_HOST=127.0.0.1 TEST_DB_NAME=mmm_test TEST_DB_USER=mmm_test \
  TEST_DB_PASS=mmm_test_pass TEST_DB_SOCKET="" vendor/bin/phpunit
```

If your MySQL instance listens on a Unix socket instead of TCP, set
`TEST_DB_SOCKET` to the socket path (default: `/tmp/test_mysql.sock`).

---

## Directory Structure

```
/
├── README.md
├── includes/                   # Core libraries and shared PHP code
│   ├── .env                    # Environment variables (not committed – see .gitignore)
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
    ├── js/                     # JavaScript (jQuery 3.7.1, lightbox, html2canvas)
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
| `includes/.env` | Local environment variables (not committed); create from the variable list in [Configuration & Environment](#configuration--environment) |
| `includes/dblib.php` | `DB::` static class wrapping MySQLi; provides `DB::connect()`, `DB::query()`, `DB::getOne()`, `DB::getAll()` etc.; also `safe()` escape helper, `BenutzerHatRecht()` permission check, and backward-compat shims for legacy `mysql_*` calls |
| `includes/getsession.php` | Reads session cookie, queries `SESSION` table, populates `$nLoginID` / `$sLogin` / `$loginID` globals |
| `includes/security.php` | Guards admin/protected pages; redirects to `login.php` if `$nLoginID` is not set |
| `includes/pelasfunctions.php` | Business logic: invoice creation, ticket status transitions, accounting helpers, PayPal fee calculation, password hashing |
| `includes/sitzlib.php` | Seating-plan library: seat lookup, reservation, block management |
| `includes/turnier/` | Tournament engine: `Turnier`, `Team`, `Match`, `Round`, `Jump`, `Ranking`, `TurnierSystem`, group and liga sub-classes |
| `includes/turnier-frontend/` | Admin-facing tournament management pages (tap, verwaltung, seeding, transfer, export, prices, admins) |
| `includes/pelasfront/` | Frontend page-logic modules shared between web roots (accounting, archiv, forum, geekradar, sitzplan, login, …) |
| `multimadness.de/admin/index.php` | Admin panel entry point |
| `multimadness.de/admin/controller.php` | Admin AJAX/action dispatcher |

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
| jQuery 3.7.1 | `multimadness.de/js/` | Frontend JS |
| Lightbox 2.11.5 | `multimadness.de/js/`, `css/`, `img/` | Image gallery |
| bxSlider 4.2.17 | `multimadness.de/js/`, `css/` | Responsive content slider |
| html2canvas | `multimadness.de/` | Client-side canvas screenshot |
| html2pdf (TCPDF-based) | `includes/html2pdf/`, `multimadness.de/html2pdf/` | PDF generation |
| phpqrcode | `includes/phpqrcode.php` | QR code generation |
| class.ezpdf / class.pdf | `includes/` | Legacy PDF generation |
| fpdf | `includes/fpdf.php` | PDF generation |

---

## Configuration & Environment

All runtime configuration lives in **`includes/constants.php`**.

### `.env` support

`constants.php` includes a minimal `.env` file loader at the top. On startup it reads `includes/.env` (if present), registering each `KEY=VALUE` pair via `putenv()`. Environment variables already set in the process take precedence over the `.env` file.

Create `includes/.env` with the variables listed in the table below and fill in your values. **Never commit `includes/.env` to version control** (it is already in `.gitignore`).

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
| `BASE_DIR` | Repository root at runtime (`dirname(__DIR__)`) — used to derive portable dev/intranet paths |
| `LOCATION` | `'internet'` or `'intranet'` (controls menu visibility) |
| `SMARTY_HOME_DIR` / `SMARTY_CLASS` | Smarty library path |
| `NEWSBILD_DIR` / `SLIDER_DIR` / `VERPFLEGUNG_DIR` / `LOCATION_DIR` / `SPONSOR_DIR` | Upload directories for image categories |
| `UPLOADDIR` | Temp directory for file uploads (`/tmp/`) |
| `MANDANTID` | Tenant/brand ID (hard-coded `2` in `index.php`) |

---

## Known Issues & Security Notes

> ⚠️ **This section documents pre-existing issues found during analysis. They are not introduced by this repository's maintainers intentionally.**

### 🔴 Critical

1. ~~**Credentials committed to source control**~~ — **Fixed.** All database passwords, mail passwords, and the Bing Maps API key are now read exclusively from environment variables via `getenv()` in `constants.php` and `index.php`. No credentials are hard-coded. Store secrets in `includes/.env` (excluded from version control by `.gitignore`).

2. ~~**Deprecated `mysql_*` functions**~~ — **Fixed.** All legacy `mysql_*` call-sites across 120+ files have been migrated to the `DB::` MySQLi wrapper (`DB::query()`, `$result->fetch_assoc()`, `DB::$link->error`, etc.). The backward-compatible shims in `includes/dblib.php` are retained for safety but are no longer exercised by application code. The underlying queries still use plain string-concatenation — see item 3.

3. ~~**SQL injection**~~ — **Fixed.** All SQL queries that previously string-interpolated user-supplied input (`$_GET`/`$_POST` variables) directly into query strings have been converted to use MySQLi prepared statements via the existing `DB::query($sql, ...$params)` interface with `?` placeholders. Affected files: `includes/pelasfront/news.php`, `includes/format.php`, `includes/pelasfront/sitzgruppen.php`.

### 🟠 High

4. ~~**No CSRF protection**~~ — **Fixed.** A per-session CSRF token (`bin2hex(random_bytes(32))`) is generated in `includes/dblib.php` (`csrf_token()`) and stored in the PHP session. `csrf_verify()` is called centrally in `includes/getsession.php` on every non-GET request and returns HTTP 403 on token mismatch. All state-changing HTML forms now embed the token via `<?= csrf_field() ?>` (PHP templates) or `{csrf_field}` (Smarty templates). External forms (e.g. PayPal) and the pre-session cookie-consent form are intentionally excluded.

5. ~~**Hard-coded absolute filesystem paths**~~ — **Fixed.** A `BASE_DIR` constant (`dirname(__DIR__)`) is now defined at the top of `constants.php` and used in the `urtyp_dev_internet` and `urtyp_dev_intranet` configuration blocks instead of the previous `/var/www.il-dev/…` hard-coded paths. `PELASDIR`, `ARCHIV_UPLOADDIR`, and `SMARTY_BASE_DIR` are all derived from `BASE_DIR` at runtime, making the dev/intranet configuration fully portable.

6. **`hostconfig.php` calls `die()`** — `includes/hostconfig.php` was removed and replaced with a `die()` stub. The only remaining reference to it is a comment in `includes/classes/PelasSmarty.class.php`; the file is no longer `require`d anywhere, so this has no runtime impact.

### 🟡 Medium

7. ~~**Error suppression with `@`**~~ — **Fixed.** The two `@DB::query(...)` calls in `includes/pelasfront/teilnahme.php` (the `update ASTATUS` and `insert into ASTATUS` statements) have been removed. Both calls have been converted to use `DB::query()` with `?` prepared-statement placeholders, so query failures now surface via the existing `DB::query()` error reporting rather than being silently swallowed by the `@` operator.

8. **Global variable pollution** — Auth state is propagated via bare global variables (`$nLoginID`, `$sLogin`, `$loginID`) that are written by `includes/getsession.php` and then read with `global $nLoginID;` declarations scattered across 24+ functions in 70+ files. There is no single authoritative accessor and no encapsulation, so any file can silently overwrite these values. A future improvement is to introduce a lightweight `Session` or `Auth` value-object that is instantiated once and passed explicitly (dependency injection) rather than relying on PHP's global scope.

9. **Inline HTML / mixed concerns** — Page modules (files under `includes/pelasfront/`, `multimadness.de/page/`, and `multimadness.de/admin/`) mix raw SQL queries, business logic, and HTML output in the same file with no separation of concerns. For example, `includes/pelasfront/teilnahme.php` issues DB queries, performs business-logic calculations, and `echo`s HTML form markup all in the same function. This tight coupling makes unit-testing individual concerns impossible without a browser/DB harness. The long-term fix is to separate controllers (routing + input validation) from models (DB/domain logic) and views (HTML templates).

10. ~~**Outdated frontend libraries**~~ — **Fixed.** All vendored JS/CSS libraries have now been updated:
    - **jQuery** updated from legacy 2.x → **3.7.1** ✅ (done previously)
    - **bxSlider** updated from **v4.1.2** → **v4.2.17** (`js/jquery.bxslider.min.js`, `css/jquery.bxslider.css`, `css/images/`) ✅
    - **Lightbox** updated from **v2.7.1 / v2.7.2** → **v2.11.5** (`js/lightbox.min.js`, `js/lightbox22.min.js`, `css/lightbox.css`, `img/close.png`, `img/loading.gif`, `img/next.png`, `img/prev.png`) ✅

### 🟢 Low / Informational

11. ~~**No automated tests**~~ — **Fixed.** A PHPUnit test suite has been added under `tests/`.  Unit tests cover pure business-logic functions (`PELAS::HashPassword`, `PayPalGebuehr`, formatting helpers, countdown), all CSRF helpers, and all tournament flag/status constants.  Integration tests cover `DB::query()`, `DB::getOne/Row/Rows/Col()`, and `PELAS::logging()`.  A GitHub Actions workflow (`.github/workflows/phpunit.yml`) runs the suite against PHP 8.1–8.3 on every push and pull request.

12. **Commented-out dead code** — Large blocks of commented-out SQL and PHP exist throughout `pelasfunctions.php`.

13. ~~**Encoding issues**~~ — **Fixed.** The double-encoded UTF-8 comment artifacts (`fÃƒÂ¼r` → `für`) in `constants.php` have been corrected.

---

## Recommended Improvements

| Priority | Action |
|---|---|
| ✅ | ~~Move all credentials/secrets to `.env` / environment variables~~ — done |
| ✅ | ~~Fix UTF-8 encoding artefacts in `constants.php`~~ — done |
| ✅ | ~~Use prepared statements (MySQLi `prepare()` / `bind_param()`) for all DB queries to eliminate SQL injection risk~~ — done |
| ✅ | ~~Replace remaining legacy `mysql_*` call-sites across 80+ files with the `DB::` MySQLi wrapper~~ — done |
| ✅ | ~~Add CSRF token generation and validation to all state-changing forms~~ — done |
| ✅ | ~~Replace hard-coded absolute paths in dev/intranet `constants.php` blocks with a single `BASE_DIR` constant derived at runtime (e.g. `dirname(__DIR__)`)~~ — done |
| ✅ | ~~Add PHPUnit test coverage for core business logic (`pelasfunctions.php`, `DB::`, tournament classes)~~ — done |
| ✅ | ~~Update or replace vendored frontend libraries~~ — jQuery 3.7.1, bxSlider 4.2.17, Lightbox 2.11.5 — done |
| ✅ | ~~Remove `@` error-suppression prefix from all `DB::query()` / MySQLi calls and add proper error handling (see item 7 in Known Issues)~~ — done |
| 🟡 | Encapsulate auth state (`$nLoginID`, `$sLogin`, `$loginID`) in a `Session`/`Auth` value-object and pass it via dependency injection instead of relying on PHP globals (see item 8) |
| 🟡 | Introduce a lightweight router/framework to separate routing, controllers, and views (see item 9) |
| 🟢 | Clean up dead/commented-out code in `pelasfunctions.php` |
