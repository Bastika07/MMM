<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the DB:: static class defined in includes/dblib.php.
 *
 * These tests require a live MySQL/MariaDB connection configured via the
 * TEST_DB_* environment variables (see tests/bootstrap.php).
 *
 * The bootstrap creates (or re-uses) the `mmm_test` database and the
 * `logging` table.
 */
class DbTest extends TestCase
{
    protected function setUp(): void
    {
        // Skip the entire suite if the DB link is not available.
        if (DB::$link === null) {
            $this->markTestSkipped('MySQL connection is not available.');
        }
        // Ensure a clean `logging` table before each test.
        DB::$link->query('DELETE FROM logging');
    }

    // ------------------------------------------------------------------
    // DB::query – plain and parameterised
    // ------------------------------------------------------------------

    public function testQueryWithoutParams(): void
    {
        $result = DB::query('SELECT 1 AS val');
        $this->assertNotFalse($result);
        $row = $result->fetch_assoc();
        $this->assertSame('1', $row['val']);
    }

    public function testQueryWithIntParam(): void
    {
        $result = DB::query('SELECT ? AS val', 42);
        $this->assertNotFalse($result);
        $row = $result->fetch_assoc();
        $this->assertEquals(42, $row['val']);
    }

    public function testQueryWithStringParam(): void
    {
        $result = DB::query('SELECT ? AS val', 'hello');
        $this->assertNotFalse($result);
        $row = $result->fetch_assoc();
        $this->assertSame('hello', $row['val']);
    }

    public function testQueryInsertAndSelect(): void
    {
        DB::query('INSERT INTO logging (userID, msg, cat) VALUES (?, ?, ?)', 1, 'test msg', 'unit-test');
        $result = DB::query('SELECT msg, cat FROM logging WHERE userID = ?', 1);
        $row = $result->fetch_assoc();
        $this->assertSame('test msg', $row['msg']);
        $this->assertSame('unit-test', $row['cat']);
    }

    // ------------------------------------------------------------------
    // DB::getOne
    // ------------------------------------------------------------------

    public function testGetOneReturnsScalar(): void
    {
        $val = DB::getOne('SELECT 123');
        $this->assertSame('123', $val);
    }

    public function testGetOneWithParam(): void
    {
        DB::query('INSERT INTO logging (msg) VALUES (?)', 'hello getone');
        $msg = DB::getOne('SELECT msg FROM logging WHERE msg = ?', 'hello getone');
        $this->assertSame('hello getone', $msg);
    }

    // ------------------------------------------------------------------
    // DB::getRow
    // ------------------------------------------------------------------

    public function testGetRowReturnsAssocArray(): void
    {
        DB::query('INSERT INTO logging (userID, msg) VALUES (?, ?)', 7, 'row-test');
        $row = DB::getRow('SELECT userID, msg FROM logging WHERE userID = ?', 7);
        $this->assertIsArray($row);
        $this->assertEquals(7, $row['userID']);
        $this->assertSame('row-test', $row['msg']);
    }

    public function testGetRowReturnsNullForNoRows(): void
    {
        $row = DB::getRow('SELECT msg FROM logging WHERE msg = ?', 'nonexistent-xyz');
        $this->assertNull($row);
    }

    // ------------------------------------------------------------------
    // DB::getRows
    // ------------------------------------------------------------------

    public function testGetRowsReturnsMultipleRows(): void
    {
        DB::query('INSERT INTO logging (msg) VALUES (?)', 'row-a');
        DB::query('INSERT INTO logging (msg) VALUES (?)', 'row-b');
        $rows = DB::getRows('SELECT msg FROM logging ORDER BY id');
        $this->assertCount(2, $rows);
        $this->assertSame('row-a', $rows[0]['msg']);
        $this->assertSame('row-b', $rows[1]['msg']);
    }

    public function testGetRowsReturnsEmptyArrayForNoRows(): void
    {
        $rows = DB::getRows('SELECT msg FROM logging WHERE msg = ?', 'nothing');
        $this->assertIsArray($rows);
        $this->assertEmpty($rows);
    }

    // ------------------------------------------------------------------
    // DB::getCol
    // ------------------------------------------------------------------

    public function testGetColReturnsFlatArray(): void
    {
        DB::query('INSERT INTO logging (msg) VALUES (?)', 'col-a');
        DB::query('INSERT INTO logging (msg) VALUES (?)', 'col-b');
        $col = DB::getCol('SELECT msg FROM logging ORDER BY id');
        $this->assertIsArray($col);
        $this->assertContains('col-a', $col);
        $this->assertContains('col-b', $col);
    }

    // ------------------------------------------------------------------
    // PELAS::logging  (uses DB::query internally)
    // ------------------------------------------------------------------

    public function testLoggingInsertsRecord(): void
    {
        // Note: DB::query()'s prepared-statement path returns the result of
        // mysqli_stmt::get_result(), which is FALSE for INSERT (no result set),
        // so PELAS::logging() returns false even on success.  We therefore
        // verify the side-effect (row was inserted) rather than the return value.
        (new PELAS())->logging('integration test message', 'test', 99);

        $row = DB::getRow(
            'SELECT msg, cat, userID FROM logging WHERE cat = ?',
            'test'
        );
        $this->assertNotNull($row);
        $this->assertSame('integration test message', $row['msg']);
        $this->assertEquals(99, $row['userID']);
    }

    public function testLoggingWithNullUserAndCategory(): void
    {
        (new PELAS())->logging('msg without context');

        $row = DB::getRow('SELECT userID, cat FROM logging WHERE msg = ?', 'msg without context');
        $this->assertNotNull($row);
        $this->assertNull($row['userID']);
        $this->assertNull($row['cat']);
    }
}
