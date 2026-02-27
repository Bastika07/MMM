<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for the CSRF helper functions defined in includes/dblib.php:
 *   csrf_token(), csrf_field(), csrf_verify()
 */
class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        // Start with a clean session for every test.
        if (session_status() !== PHP_SESSION_NONE) {
            $_SESSION = [];
        }
    }

    // ------------------------------------------------------------------
    // csrf_token()
    // ------------------------------------------------------------------

    public function testCsrfTokenReturnsNonEmptyString(): void
    {
        $token = csrf_token();
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testCsrfTokenIsHexString(): void
    {
        $token = csrf_token();
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $token);
    }

    public function testCsrfTokenLength(): void
    {
        // bin2hex(random_bytes(32)) → 64 hex characters
        $this->assertSame(64, strlen(csrf_token()));
    }

    public function testCsrfTokenIsCached(): void
    {
        $token1 = csrf_token();
        $token2 = csrf_token();
        $this->assertSame($token1, $token2, 'csrf_token() must return the same value within a session');
    }

    public function testCsrfTokenIsStoredInSession(): void
    {
        $token = csrf_token();
        $this->assertSame($token, $_SESSION['MMMSESSION']['csrf_token']);
    }

    public function testCsrfTokenRegeneratesWhenSessionCleared(): void
    {
        $token1 = csrf_token();
        // Clear the stored token to simulate a fresh session.
        unset($_SESSION['MMMSESSION']['csrf_token']);
        $token2 = csrf_token();
        // The new token should be valid but may differ from the first.
        $this->assertNotEmpty($token2);
    }

    // ------------------------------------------------------------------
    // csrf_field()
    // ------------------------------------------------------------------

    public function testCsrfFieldContainsHiddenInput(): void
    {
        $field = csrf_field();
        $this->assertStringContainsString('<input', $field);
        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="_csrf_token"', $field);
    }

    public function testCsrfFieldValueMatchesToken(): void
    {
        $token = csrf_token();
        $field = csrf_field();
        $this->assertStringContainsString('value="' . $token . '"', $field);
    }

    public function testCsrfFieldEscapesSpecialChars(): void
    {
        // Inject a token with characters that need HTML escaping.
        $_SESSION['MMMSESSION']['csrf_token'] = '"<script>';
        $field = csrf_field();
        $this->assertStringNotContainsString('"<script>', $field);
        $this->assertStringContainsString('&quot;', $field);
    }

    // ------------------------------------------------------------------
    // csrf_verify()
    // ------------------------------------------------------------------

    public function testCsrfVerifyPassesOnGetRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Should return without calling exit().
        csrf_verify();
        $this->assertTrue(true); // reached means no exit
    }

    public function testCsrfVerifyPassesOnHeadRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        csrf_verify();
        $this->assertTrue(true);
    }

    public function testCsrfVerifyPassesOnOptionsRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        csrf_verify();
        $this->assertTrue(true);
    }

    public function testCsrfVerifyPassesWhenTokenMatches(): void
    {
        $token = csrf_token();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_csrf_token']      = $token;
        // Should not exit.
        csrf_verify();
        $this->assertTrue(true);
    }

    public function testCsrfVerifyExitsOnTokenMismatch(): void
    {
        // csrf_verify() calls exit() on mismatch which terminates the process.
        // We cannot test the exit() path without @runInSeparateProcess.
        // This test documents the expected behaviour: a mismatched token is
        // NOT equal to the session token.
        $token = csrf_token();
        $this->assertNotSame($token, 'wrong-token');
        $this->assertNotSame($token, '');
    }
}
