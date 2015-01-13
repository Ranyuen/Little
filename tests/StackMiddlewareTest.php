<?php
require_once 'tests/Fixture/HelloMiddleware.php';

use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class StackMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function testStackMiddleware()
    {
        $r = new Router();
        $r->pushStack('Fixture\HelloMiddleware');
        $r->get('/', function ($name) { return $name; });
        $req = Request::create('/');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Hello World', $res->getContent());
    }

    public function testStackMiddlewareWithGroup()
    {
        $r = new Router();
        $r->pushStack('Fixture\HelloMiddleware');
        $r->get('/', function () { return 'index'; });
        $r->group('/mOmonga', function ($r) {
            $r->pushStack('Fixture\HelloMiddleware');
            $r->get('/', function () { return 'mOmonga'; });
        });

        $req = Request::create('/');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Hello index', $res->getContent());

        $req = Request::create('/mOmonga/');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Hello Hello mOmonga', $res->getContent());
    }
}
