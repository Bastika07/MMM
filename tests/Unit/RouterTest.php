<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Router class defined in
 * includes/classes/Router.class.php.
 */
class RouterTest extends TestCase
{
    // ------------------------------------------------------------------
    // add() / has()
    // ------------------------------------------------------------------

    public function testHasReturnsFalseForUnregisteredId(): void
    {
        $router = new Router();
        $this->assertFalse($router->has(99));
    }

    public function testHasReturnsTrueAfterAdd(): void
    {
        $router = new Router();
        $router->add(1, 'start');
        $this->assertTrue($router->has(1));
    }

    public function testAddReturnsSameInstance(): void
    {
        $router = new Router();
        $this->assertSame($router, $router->add(1, 'start'));
    }

    public function testAddSupportsChainingMultipleRoutes(): void
    {
        $router = (new Router())
            ->add(1, 'start')
            ->add(2, 'news')
            ->add(5, 'login');

        $this->assertTrue($router->has(1));
        $this->assertTrue($router->has(2));
        $this->assertTrue($router->has(5));
        $this->assertFalse($router->has(3));
    }

    // ------------------------------------------------------------------
    // resolve()
    // ------------------------------------------------------------------

    public function testResolveReturnsModuleForRegisteredId(): void
    {
        $router = (new Router())->add(2, 'news');
        $this->assertSame('news', $router->resolve(2));
    }

    public function testResolveReturnsSubdirectoryModule(): void
    {
        $router = (new Router())->add(20, 'turnier/turnier_list');
        $this->assertSame('turnier/turnier_list', $router->resolve(20));
    }

    public function testResolveReturnsDefaultRouteForUnknownId(): void
    {
        $router = (new Router(1))->add(1, 'start')->add(2, 'news');
        $this->assertSame('start', $router->resolve(999));
    }

    public function testResolveReturnsFallbackErrorWhenDefaultIdNotRegistered(): void
    {
        $router = new Router(1); // default is 1 but no routes registered
        $this->assertSame('error', $router->resolve(42));
    }

    public function testResolveUsesCustomDefaultId(): void
    {
        $router = (new Router(999))
            ->add(999, 'error')
            ->add(1,   'start');
        $this->assertSame('error', $router->resolve(55));
    }

    // ------------------------------------------------------------------
    // getRoutes()
    // ------------------------------------------------------------------

    public function testGetRoutesReturnsEmptyArrayInitially(): void
    {
        $router = new Router();
        $this->assertSame([], $router->getRoutes());
    }

    public function testGetRoutesReturnsAllRegisteredRoutes(): void
    {
        $router = (new Router())
            ->add(1, 'start')
            ->add(2, 'news');

        $expected = [1 => 'start', 2 => 'news'];
        $this->assertSame($expected, $router->getRoutes());
    }

    // ------------------------------------------------------------------
    // getDefaultId()
    // ------------------------------------------------------------------

    public function testGetDefaultIdReturnsConstructorValue(): void
    {
        $router = new Router(5);
        $this->assertSame(5, $router->getDefaultId());
    }

    public function testGetDefaultIdDefaultsToOne(): void
    {
        $router = new Router();
        $this->assertSame(1, $router->getDefaultId());
    }

    // ------------------------------------------------------------------
    // Full route table (mirrors the production route map in index.php)
    // ------------------------------------------------------------------

    public function testProductionRouteTableRegistersExpectedPages(): void
    {
        $router = (new Router())
            ->add(1,   'start')
            ->add(111, 'start2')
            ->add(2,   'news')
            ->add(3,   'info')
            ->add(4,   'benutzerdetails')
            ->add(5,   'login')
            ->add(6,   'accounting')
            ->add(7,   'accounting_rechnung')
            ->add(8,   'teilnehmerliste')
            ->add(9,   'sitzplan')
            ->add(10,  'forum')
            ->add(11,  'login_edit')
            ->add(12,  'forum')
            ->add(13,  'sitzplan')
            ->add(14,  'archiv')
            ->add(15,  'archiv_upload')
            ->add(16,  'geekradar')
            ->add(17,  'kontaktformular')
            ->add(18,  'clanverwaltung')
            ->add(19,  'clandetails')
            ->add(20,  'turnier/turnier_list')
            ->add(21,  'turnier/turnier_detail')
            ->add(22,  'turnier/turnier_faq')
            ->add(23,  'turnier/turnier_ranking')
            ->add(24,  'turnier/turnier_table')
            ->add(25,  'turnier/turnier_tree')
            ->add(26,  'turnier/match_detail')
            ->add(27,  'turnier/team_create')
            ->add(28,  'turnier/team_create2')
            ->add(29,  'turnier/team_detail')
            ->add(30,  'turnier/team_swap')
            ->add(31,  'gastserver')
            ->add(32,  'umfrage')
            ->add(40,  'lokation')
            ->add(41,  'netzwerk')
            ->add(42,  'bedingungen')
            ->add(43,  'impressum')
            ->add(44,  'team')
            ->add(45,  'verpflegung')
            ->add(46,  'umgebungskarte')
            ->add(47,  'datenschutz')
            ->add(48,  'sponsoren')
            ->add(49,  'shirtshop')
            ->add(99,  'sitzplanv2')
            ->add(500, 'covid19')
            ->add(999, 'error');

        $this->assertSame('start',                $router->resolve(1));
        $this->assertSame('news',                 $router->resolve(2));
        $this->assertSame('turnier/turnier_list', $router->resolve(20));
        $this->assertSame('sitzplanv2',           $router->resolve(99));
        $this->assertSame('covid19',              $router->resolve(500));
        $this->assertSame('error',                $router->resolve(999));
        // Unknown page ID → default (1 → 'start')
        $this->assertSame('start',                $router->resolve(777));
    }
}
