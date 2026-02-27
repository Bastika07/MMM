<?php

/**
 * Immutable value-object encapsulating the authenticated-user state that was
 * previously propagated via the bare global variables $nLoginID, $sLogin,
 * and $loginID.
 *
 * An instance is created by getsession.php once the session has been resolved
 * and is accessible via the module-level $authState variable.  All existing
 * global variables ($nLoginID, $sLogin, $loginID) are kept for backward
 * compatibility; new code should use AuthState via dependency injection.
 *
 * Usage:
 *   // Read current auth state (set by getsession.php):
 *   global $authState;
 *   if ($authState->isLoggedIn()) { ... }
 *
 *   // Pass explicitly to a function:
 *   function myFunc(AuthState $auth) { ... }
 */
class AuthState
{
    /** Numeric user-ID; 0 means not logged in. */
    public readonly int $nLoginID;

    /** Login name; empty string when not logged in. */
    public readonly string $sLogin;

    public function __construct(int|string $nLoginID, string $sLogin)
    {
        $this->nLoginID = (int) $nLoginID;
        $this->sLogin   = $sLogin;
    }

    /**
     * Returns true when a user is authenticated (nLoginID > 0).
     */
    public function isLoggedIn(): bool
    {
        return $this->nLoginID > 0;
    }

    /**
     * Alias for nLoginID kept for code that previously used $loginID.
     */
    public function getLoginID(): int
    {
        return $this->nLoginID;
    }
}
