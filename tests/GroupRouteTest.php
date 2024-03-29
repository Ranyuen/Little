<?php

use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class GroupRouteTest extends \PHPUnit\Framework\TestCase
{
    /**
     */
    public function testBasicGroup()
    {
        $child = new Router();
        $child->get('/:id', function ($id) {
            return "GET $id";
        });
        $child->post('/:id', function ($id) {
            return "POST $id";
        });
        $r = new Router();
        $r->group('/user', $child);

        $req = Request::create('/user/42');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('GET 42', $res->getContent());

        $req = Request::create('/user/42', 'POST');
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
            $r->get('/:id', function ($id) {
                return "GET $id";
            });
            $r->post('/:id', function ($id) {
                return "POST $id";
            });
        });

        $req = Request::create('/user/42');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('GET 42', $res->getContent());

        $req = Request::create('/user/42', 'POST');
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
            $r->get('/', function () {
                throw new Exception();
            });
            $r->get('/503', function ($router, $req) {
                return $router->error(503, $req);
            });
            $r->error(500, function () {
                return 'Member Error';
            });
        });
        $r->error(500, function () {
            return 'All 500 Error';
        });
        $r->error(503, function () {
            return 'All 503 Error';
        });

        $req = Request::create('/member/');
        $res = $r->run($req);
        $this->assertEquals(500, $res->getStatusCode());
        $this->assertEquals('Member Error', $res->getContent());

        $req = Request::create('/member/503');
        $res = $r->run($req);
        $this->assertEquals(503, $res->getStatusCode());
        $this->assertEquals('All 503 Error', $res->getContent());
    }

    public function testSeparateRouter()
    {
        $child = new Router();
        $child->get('/', function () {
            return '';
        });
        $r = new Router();
        $r->group('/user', $child);

        $req = Request::create('/user/');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());

        $req = Request::create('/');
        $res = $child->run($req);
        $this->assertEquals(200, $res->getStatusCode());

        $req = Request::create('/user/');
        $res = $child->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = Request::create('/');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());
    }
}
