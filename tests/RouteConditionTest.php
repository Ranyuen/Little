<?php

use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class RouteConditionTest extends PHPUnit_Framework_TestCase
{
    public function testRouteCondition()
    {
        $test = $this;
        $r = new Router();
        $r->get('/:id', function () { return ''; })
            ->assert(function ($req, $request, Request $q, $router, Router $r, $id) use ($test) {
                $test->assertInstanceOf('Ranyuen\Little\Request', $req);
                $test->assertInstanceOf('Ranyuen\Little\Request', $request);
                $test->assertInstanceOf('Ranyuen\Little\Request', $q);
                $test->assertInstanceOf('Ranyuen\Little\Router', $router);
                $test->assertInstanceOf('Ranyuen\Little\Router', $r);

                return 42 === (int) $id;
            });

        $req = Request::create('/42');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());

        $req = Request::create('/41');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testParamCondition()
    {
        $r = new Router();
        $r->get('/:id', function () { return ''; })
            ->assert('id', '42');

        $req = Request::create('/42');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());

        $req = Request::create('/41');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $r = new Router();
        $r->get('/:id', function () { return ''; })
            ->assert('id', '/\A\d+\z/');

        $req = Request::create('/42');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());

        $req = Request::create('/id');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());
    }
}
