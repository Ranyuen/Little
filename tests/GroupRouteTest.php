<?php

use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class GroupRouteTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testBasicGroup()
    {
        $child = new Router();
        $child->get('/{id}', function ($id) { return "GET $id"; });
        $child->post('/{id}', function ($id) { return "POST $id"; });
        $r = new Router();
        $r->group('/user', $child);

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/user/42']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('GET 42', $res->getContent());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/user/42']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('POST 42', $res->getContent());
    }

    /**
     */
    public function testGroupMethod()
    {
        $r = new Router();
        $r->group('/user', function ($r) {
            $r->get('/{id}', function ($id) { return "GET $id"; });
            $r->post('/{id}', function ($id) { return "POST $id"; });
        });

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/user/42']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('GET 42', $res->getContent());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/user/42']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('POST 42', $res->getContent());
    }

    /**
     */
    public function testGroupError()
    {
        $r = new Router();
        $r->group('/member', function ($r) {
            $r->get('/', function () { throw new Exception(); });
            $r->get('/503', function ($router, $req) {
                return $router->error(503, $req);
            });
            $r->error(500, function () { return 'Member Error'; });
        });
        $r->error(500, function () { return 'All 500 Error'; });
        $r->error(503, function () { return 'All 503 Error'; });

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/member/']
        );
        $res = $r->run($req);
        $this->assertEquals(500, $res->getStatusCode());
        $this->assertEquals('Member Error', $res->getContent());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/member/503']
        );
        $res = $r->run($req);
        $this->assertEquals(503, $res->getStatusCode());
        $this->assertEquals('All 503 Error', $res->getContent());
    }
}
