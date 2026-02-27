<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for pure (database-free) methods of the PELAS class defined in
 * includes/dblib.php.
 */
class PelasTest extends TestCase
{
    // ------------------------------------------------------------------
    // PELAS::HashPassword
    // ------------------------------------------------------------------

    public function testHashPasswordReturnsNonEmptyString(): void
    {
        $hash = (new PELAS())->HashPassword('secret', 42);
        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
    }

    public function testHashPasswordIsDeterministic(): void
    {
        $hash1 = (new PELAS())->HashPassword('secret', 42);
        $hash2 = (new PELAS())->HashPassword('secret', 42);
        $this->assertSame($hash1, $hash2);
    }

    public function testHashPasswordDiffersForDifferentInputs(): void
    {
        $a = (new PELAS())->HashPassword('password1', 1);
        $b = (new PELAS())->HashPassword('password2', 1);
        $c = (new PELAS())->HashPassword('password1', 2);
        $this->assertNotSame($a, $b, 'Different passwords must produce different hashes');
        $this->assertNotSame($a, $c, 'Same password with different user ID must produce different hashes');
    }

    public function testHashPasswordUsesBase64Salt(): void
    {
        // The salt is base64_encode($userID), so we can replicate the logic
        // independently and compare.
        $password = 'testpass';
        $userID   = 99;
        $salt     = base64_encode((string) $userID);
        $expected = sha1($salt . $password);
        $this->assertSame($expected, (new PELAS())->HashPassword($password, $userID));
    }

    // ------------------------------------------------------------------
    // PELAS::PayPalGebuehr
    // ------------------------------------------------------------------

    public function testPayPalGebuehrZero(): void
    {
        // 0 / 100 * 1.9 + 0.35 = 0.35 * 1.19 = 0.4165 → round to 0.42
        $fee = (new PELAS())->PayPalGebuehr(0);
        $this->assertSame(0.42, $fee);
    }

    public function testPayPalGebuehrKnownValue(): void
    {
        // 100 € ticket: 100/100*1.9 + 0.35 = 2.25, * 1.19 = 2.6775 → 2.68
        $fee = (new PELAS())->PayPalGebuehr(100.00);
        $this->assertSame(2.68, $fee);
    }

    public function testPayPalGebuehrIsRoundedToTwoDecimals(): void
    {
        $fee = (new PELAS())->PayPalGebuehr(50.00);
        // 50/100*1.9 + 0.35 = 1.30, * 1.19 = 1.547 → 1.55
        $this->assertSame(1.55, $fee);
        // Ensure exactly two decimal places.
        $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', (string) $fee);
    }

    // ------------------------------------------------------------------
    // PELAS::formatBestellNr
    // ------------------------------------------------------------------

    public function testFormatBestellNrPadsNumbers(): void
    {
        $result = (new PELAS())->formatBestellNr(2, 1);
        $this->assertSame('0200001', $result);
    }

    public function testFormatBestellNrLargeValues(): void
    {
        $result = (new PELAS())->formatBestellNr(10, 99999);
        $this->assertSame('1099999', $result);
    }

    public function testFormatBestellNrLength(): void
    {
        $result = (new PELAS())->formatBestellNr(1, 1);
        // partyId = 2 digits, bestNr = 5 digits → 7 chars total
        $this->assertSame(7, strlen($result));
    }

    // ------------------------------------------------------------------
    // PELAS::getPartyIdFromBestNr / getBestellIdFromBestNr
    // ------------------------------------------------------------------

    public function testRoundTripBestellNr(): void
    {
        $partyId  = 5;
        $bestellId = 12345;
        $formatted = (new PELAS())->formatBestellNr($partyId, $bestellId);

        $this->assertSame($partyId,   (new PELAS())->getPartyIdFromBestNr($formatted));
        $this->assertSame($bestellId, (new PELAS())->getBestellIdFromBestNr($formatted));
    }

    public function testGetPartyIdFromBestNr(): void
    {
        $this->assertSame(2,  (new PELAS())->getPartyIdFromBestNr('0200001'));
        $this->assertSame(10, (new PELAS())->getPartyIdFromBestNr('1099999'));
    }

    public function testGetBestellIdFromBestNr(): void
    {
        $this->assertSame(1,     (new PELAS())->getBestellIdFromBestNr('0200001'));
        $this->assertSame(99999, (new PELAS())->getBestellIdFromBestNr('1099999'));
    }

    // ------------------------------------------------------------------
    // PELAS::formatTicketNr
    // ------------------------------------------------------------------

    public function testFormatTicketNrPads(): void
    {
        $this->assertSame('00001', (new PELAS())->formatTicketNr(1));
        $this->assertSame('00099', (new PELAS())->formatTicketNr(99));
        $this->assertSame('99999', (new PELAS())->formatTicketNr(99999));
    }

    public function testFormatTicketNrIsAlwaysFiveChars(): void
    {
        foreach ([1, 10, 100, 1000, 10000] as $n) {
            $this->assertSame(5, strlen((new PELAS())->formatTicketNr($n)));
        }
    }

    // ------------------------------------------------------------------
    // PELAS::userLink
    // ------------------------------------------------------------------

    public function testUserLinkNumericId(): void
    {
        $link = (new PELAS())->userLink(42);
        $this->assertStringContainsString('42', $link);
        $this->assertStringContainsString('benutzerdetails.php', $link);
    }

    public function testUserLinkNonNumericId(): void
    {
        $link = (new PELAS())->userLink('not-a-number');
        $this->assertSame('ID fehlt', $link);
    }

    // ------------------------------------------------------------------
    // PELAS::countdown
    // ------------------------------------------------------------------

    public function testCountdownReturnsFourKeys(): void
    {
        $now = time();
        $rc  = (new PELAS())->countdown($now + 90061); // 1d 1h 1m 1s
        $this->assertArrayHasKey('days',    $rc);
        $this->assertArrayHasKey('hours',   $rc);
        $this->assertArrayHasKey('minutes', $rc);
        $this->assertArrayHasKey('seconds', $rc);
    }

    public function testCountdownOneDay(): void
    {
        $now = time();
        $rc  = (new PELAS())->countdown($now + 86400);
        $this->assertEquals(1, $rc['days']);
        $this->assertEquals(0, $rc['hours']);
        $this->assertEquals(0, $rc['minutes']);
    }

    public function testCountdownOneHour(): void
    {
        $now = time();
        $rc  = (new PELAS())->countdown($now + 3600);
        $this->assertEquals(0, $rc['days']);
        $this->assertEquals(1, $rc['hours']);
        $this->assertEquals(0, $rc['minutes']);
    }

    public function testCountdownOneMinute(): void
    {
        $now = time();
        $rc  = (new PELAS())->countdown($now + 60);
        $this->assertEquals(0, $rc['days']);
        $this->assertEquals(0, $rc['hours']);
        $this->assertEquals(1, $rc['minutes']);
    }
}
