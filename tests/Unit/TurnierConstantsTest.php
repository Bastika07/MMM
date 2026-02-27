<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for the constants defined in includes/turnier/t_constants.php.
 *
 * The bootstrap loads dblib.php which in turn leads to t_constants.php
 * NOT being auto-included; we explicitly load it here.
 */
class TurnierConstantsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../includes/turnier/t_constants.php';
    }

    // ------------------------------------------------------------------
    // General error/success flags
    // ------------------------------------------------------------------

    public function testTsSuccessIsZero(): void
    {
        $this->assertSame(0, TS_SUCCESS);
    }

    public function testTsErrorIsBitFlag(): void
    {
        $this->assertSame(0x80000000, TS_ERROR);
        $this->assertGreaterThan(0, TS_ERROR);
    }

    public function testAllErrorCodesHaveTsErrorBitSet(): void
    {
        $errorConstants = [
            TS_REG_CLOSED, TS_NOT_LOGGED_IN, TS_NOT_PAYED,
            TS_ALREADY_REG, TS_TOO_FEW_COINS, TS_TOO_MANY_TEAMS,
            TS_NOT_LEADER, TS_TEAM_FULL, TS_NOT_QUEUED,
            TS_NOT_MEMBER, TS_IS_LEADER, TS_NO_SUCH_TEAM,
            TS_DUP_TEAM, TS_DUP_LEAGUE_ID, TS_NOT_ADMIN,
            TS_DUP_PENALTY, TS_DUP_RESULT, TS_MATCH_NOT_READY,
            TS_TOURNEY_NOT_RUNNING, TS_TOURNEY_RUNNING,
            TS_TEAMNAME_EMPTY, TS_RESOLVE_IDS,
        ];
        foreach ($errorConstants as $code) {
            $this->assertSame(TS_ERROR, $code & TS_ERROR,
                "Error code 0x" . dechex($code) . " must have TS_ERROR bit set");
        }
    }

    public function testAllErrorCodesAreUnique(): void
    {
        $errorConstants = [
            TS_REG_CLOSED, TS_NOT_LOGGED_IN, TS_NOT_PAYED,
            TS_ALREADY_REG, TS_TOO_FEW_COINS, TS_TOO_MANY_TEAMS,
            TS_NOT_LEADER, TS_TEAM_FULL, TS_NOT_QUEUED,
            TS_NOT_MEMBER, TS_IS_LEADER, TS_NO_SUCH_TEAM,
            TS_DUP_TEAM, TS_DUP_LEAGUE_ID, TS_NOT_ADMIN,
            TS_DUP_PENALTY, TS_DUP_RESULT, TS_MATCH_NOT_READY,
            TS_TOURNEY_NOT_RUNNING, TS_TOURNEY_RUNNING,
            TS_TEAMNAME_EMPTY, TS_RESOLVE_IDS,
        ];
        $this->assertSame(
            count($errorConstants),
            count(array_unique($errorConstants)),
            'All TS_* error constants must have unique values'
        );
    }

    // ------------------------------------------------------------------
    // Match flags
    // ------------------------------------------------------------------

    public function testMatchFlagsArePowersOfTwo(): void
    {
        $flags = [
            MATCH_UNKNOWN, MATCH_READY, MATCH_PLAYING, MATCH_COMPLETE,
            MATCH_TEAM1_ACCEPT, MATCH_TEAM2_ACCEPT,
            MATCH_TEAM1_SEEDED, MATCH_TEAM2_SEEDED,
            MATCH_USER_RESULT, MATCH_ADMIN_RESULT, MATCH_RANDOM_RESULT,
        ];
        foreach ($flags as $flag) {
            if ($flag === MATCH_UNKNOWN) {
                $this->assertSame(0, $flag);
                continue;
            }
            // A power of two has exactly one bit set: (n & (n-1)) == 0
            $this->assertSame(0, $flag & ($flag - 1),
                "MATCH flag 0x" . dechex($flag) . " must be a power of two");
        }
    }

    public function testMatchFlagsAreUnique(): void
    {
        $flags = [
            MATCH_READY, MATCH_PLAYING, MATCH_COMPLETE,
            MATCH_TEAM1_ACCEPT, MATCH_TEAM2_ACCEPT,
            MATCH_TEAM1_SEEDED, MATCH_TEAM2_SEEDED,
            MATCH_USER_RESULT, MATCH_ADMIN_RESULT, MATCH_RANDOM_RESULT,
            MATCH_TEAM1_GELB, MATCH_TEAM2_GELB,
            MATCH_TEAM1_ROT,  MATCH_TEAM2_ROT,
        ];
        $this->assertSame(
            count($flags),
            count(array_unique($flags)),
            'All MATCH_* flags must be unique'
        );
    }

    // ------------------------------------------------------------------
    // Tournament type flags
    // ------------------------------------------------------------------

    public function testTurnierTypeFlagsExist(): void
    {
        $this->assertSame(0x10, TURNIER_SINGLE);
        $this->assertSame(0x20, TURNIER_DOUBLE);
        $this->assertSame(0x40, TURNIER_RUNDEN);
    }

    public function testTurnierStatusCodesAreSequential(): void
    {
        $statuses = [
            TURNIER_STAT_RES_NOT_OPEN,
            TURNIER_STAT_RES_OPEN,
            TURNIER_STAT_RES_CLOSED,
            TURNIER_STAT_SEEDING,
            TURNIER_STAT_RUNNING,
            TURNIER_STAT_PAUSED,
            TURNIER_STAT_FINISHED,
            TURNIER_STAT_CANCELED,
        ];
        foreach ($statuses as $index => $status) {
            $this->assertSame($index, $status,
                "Tournament status #{$index} must equal its index");
        }
    }

    // ------------------------------------------------------------------
    // Team2User flags
    // ------------------------------------------------------------------

    public function testTeam2UserFlagsAreBitmask(): void
    {
        // QUEUED and MEMBER must not overlap
        $this->assertSame(0, TEAM2USER_QUEUED & TEAM2USER_MEMBER);
        // MEMBER and LEADER must not overlap
        $this->assertSame(0, TEAM2USER_MEMBER & TEAM2USER_LEADER);
    }

    // ------------------------------------------------------------------
    // Liga type constants
    // ------------------------------------------------------------------

    public function testLigaTypeConstantsExist(): void
    {
        $this->assertSame(0, TURNIER_LIGA_NORMAL);
        $this->assertSame(1, TURNIER_LIGA_FUN);
        $this->assertSame(2, TURNIER_LIGA_WWCL);
        $this->assertSame(3, TURNIER_LIGA_NGL);
    }
}
