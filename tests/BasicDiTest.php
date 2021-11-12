<?php

require_once 'tests/Fixture/Momonga.php';

use Fixture\Momonga;
use Ranyuen\Di\Container;
use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class BasicDiTest extends \PHPUnit\Framework\TestCase
{
    /**
     */
    public function testDiByName()
    {
        $c = new Container(['momonga' => 'mOmonga']);
        $r = new Router($c);
        $r->get('/', function ($momonga) {
            return $momonga;
        });
        $req = Request::create('/');
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
        $req = Request::create('/');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
    }
}
