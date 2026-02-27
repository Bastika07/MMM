<?php
/**
 * PHPUnit bootstrap file.
 *
 * Sets up the test environment so that includes/dblib.php and related files
 * can be loaded.  Both test suites need a working MySQL connection because
 * DB::connect() is called at the file level inside dblib.php.
 *
 * Connection is configured via environment variables (defaults for the local
 * development setup that uses a socket):
 *
 *   TEST_DB_HOST    127.0.0.1           (use 'localhost' with TEST_DB_SOCKET)
 *   TEST_DB_NAME    mmm_test
 *   TEST_DB_USER    mmm_test
 *   TEST_DB_PASS    mmm_test_pass
 *   TEST_DB_SOCKET  /tmp/test_mysql.sock  (leave empty for TCP)
 *
 * In GitHub Actions the mysql service uses TCP on 127.0.0.1:3306, so only
 * TEST_DB_HOST / TEST_DB_NAME / TEST_DB_USER / TEST_DB_PASS need to be set.
 */

// ---------------------------------------------------------------------------
// 1. Session – CSRF helpers need $_SESSION
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------------------
// 2. Point the DB layer at the test database via environment variables that
//    constants.php reads when php_sapi_name() == 'cli'.
// ---------------------------------------------------------------------------
$testSocket = getenv('TEST_DB_SOCKET') !== false ? getenv('TEST_DB_SOCKET') : '/tmp/test_mysql.sock';
$testHost   = getenv('TEST_DB_HOST')   !== false ? getenv('TEST_DB_HOST')   : 'localhost';
$testName   = getenv('TEST_DB_NAME')   !== false ? getenv('TEST_DB_NAME')   : 'mmm_test';
$testUser   = getenv('TEST_DB_USER')   !== false ? getenv('TEST_DB_USER')   : 'mmm_test';
$testPass   = getenv('TEST_DB_PASS')   !== false ? getenv('TEST_DB_PASS')   : 'mmm_test_pass';

// When a socket path is given, MySQLi uses it when host is 'localhost'.
if ($testSocket !== '') {
    ini_set('mysqli.default_socket', $testSocket);
    $testHost = 'localhost';
}

// Inject credentials as LIVE_* env vars so that constants.php picks them up.
putenv("LIVE_DB_HOST={$testHost}");
putenv("LIVE_DB_NAME={$testName}");
putenv("LIVE_DB_USER={$testUser}");
putenv("LIVE_DB_PASS={$testPass}");

// Also write directly into $GLOBALS so that CFG::get('mysql') – which uses
// `global $dbhost, $dbname, $dbuser, $dbpass` – finds the test credentials
// even when PHPUnit loads this bootstrap from inside a method scope (where
// variables set by include'd files would be local, not global).
$GLOBALS['dbhost'] = $testHost;
$GLOBALS['dbname'] = $testName;
$GLOBALS['dbuser'] = $testUser;
$GLOBALS['dbpass'] = $testPass;

// ---------------------------------------------------------------------------
// 3. Load the application code.
//    dblib.php uses require_once('constants.php') with a relative path, so
//    it must be loaded from the includes/ directory context.
// ---------------------------------------------------------------------------
$_SERVER['SERVER_NAME'] = $testHost;

chdir(__DIR__ . '/../includes');
require_once __DIR__ . '/../includes/dblib.php';
chdir(__DIR__ . '/..');

// ---------------------------------------------------------------------------
// 4. Autoloader (Composer)
// ---------------------------------------------------------------------------
require_once __DIR__ . '/../vendor/autoload.php';
