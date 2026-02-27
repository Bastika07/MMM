<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the AuthState value-object defined in
 * includes/classes/AuthState.class.php.
 */
class AuthStateTest extends TestCase
{
    // ------------------------------------------------------------------
    // Constructor / property access
    // ------------------------------------------------------------------

    public function testConstructorSetsNLoginID(): void
    {
        $auth = new AuthState(42, 'alice');
        $this->assertSame(42, $auth->nLoginID);
    }

    public function testConstructorSetsSLogin(): void
    {
        $auth = new AuthState(42, 'alice');
        $this->assertSame('alice', $auth->sLogin);
    }

    public function testConstructorCastsStringIdToInt(): void
    {
        $auth = new AuthState('7', 'bob');
        $this->assertSame(7, $auth->nLoginID);
    }

    public function testConstructorAcceptsZeroAsGuest(): void
    {
        $auth = new AuthState(0, '');
        $this->assertSame(0, $auth->nLoginID);
        $this->assertSame('', $auth->sLogin);
    }

    public function testConstructorCastsEmptyStringIdToZero(): void
    {
        $auth = new AuthState('', '');
        $this->assertSame(0, $auth->nLoginID);
    }

    // ------------------------------------------------------------------
    // isLoggedIn()
    // ------------------------------------------------------------------

    public function testIsLoggedInReturnsTrueForPositiveId(): void
    {
        $auth = new AuthState(1, 'user');
        $this->assertTrue($auth->isLoggedIn());
    }

    public function testIsLoggedInReturnsFalseForZeroId(): void
    {
        $auth = new AuthState(0, '');
        $this->assertFalse($auth->isLoggedIn());
    }

    public function testIsLoggedInReturnsFalseForEmptyStringId(): void
    {
        $auth = new AuthState('', '');
        $this->assertFalse($auth->isLoggedIn());
    }

    // ------------------------------------------------------------------
    // getLoginID()
    // ------------------------------------------------------------------

    public function testGetLoginIDMatchesNLoginID(): void
    {
        $auth = new AuthState(99, 'carol');
        $this->assertSame($auth->nLoginID, $auth->getLoginID());
    }

    public function testGetLoginIDReturnsIntForStringInput(): void
    {
        $auth = new AuthState('55', 'dave');
        $this->assertSame(55, $auth->getLoginID());
    }

    // ------------------------------------------------------------------
    // Immutability (readonly properties)
    // ------------------------------------------------------------------

    public function testNLoginIDPropertyIsReadonly(): void
    {
        $auth = new AuthState(10, 'eve');
        $this->expectException(\Error::class);
        // @phpstan-ignore-next-line
        $auth->nLoginID = 99;
    }

    public function testSLoginPropertyIsReadonly(): void
    {
        $auth = new AuthState(10, 'eve');
        $this->expectException(\Error::class);
        // @phpstan-ignore-next-line
        $auth->sLogin = 'other';
    }
}
