<?php
require_once 'tests/Fixture/Momonga.php';

use Fixture\Momonga;
use Ranyuen\Di\Container;
use Ranyuen\Little\Router;
use Symfony\Component\HttpFoundation\Request;

class BasicDiTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testDiByName()
    {
        $c = new Container(['momonga' => 'mOmonga']);
        $r = new Router($c);
        $r->get('/', function ($momonga) { return $momonga; });
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('mOmonga', $res->getContent());
    }

    /**
     */
    public function testDiByType()
    {
        $test = $this;
        $c = new Container();
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Momonga();
        });
        $r = new Router($c);
        $r->get('/', function (Momonga $m) use ($test, $c) {
            $test->assertSame($c['momonga'], $m);

            return '';
        });
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
    }
}
