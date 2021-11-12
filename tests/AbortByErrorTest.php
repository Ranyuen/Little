<?php

use Ranyuen\Little\Exception\Found;
use Ranyuen\Little\Exception\Forbidden;
use Ranyuen\Little\Exception\MovedPermanently;
use Ranyuen\Little\Exception\NotFound;
use Ranyuen\Little\Exception\RequestEntityTooLarge;
use Ranyuen\Little\Exception\SeeOther;
use Ranyuen\Little\Exception\ServiceUnavailable;
use Ranyuen\Little\Exception\TemporaryRedirect;
use Ranyuen\Little\Exception\Unauthorized;
use Ranyuen\Little\Exception\UnprocessableEntity;
use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class AbortByErrorTest extends \PHPUnit\Framework\TestCase
{
    public function test301()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new MovedPermanently('/301');
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(301, $res->getStatusCode());
        $this->assertEquals('/301', $res->headers->get('Location'));
    }

    public function test302()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new Found('/302');
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(302, $res->getStatusCode());
        $this->assertEquals('/302', $res->headers->get('Location'));
    }

    public function test303()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new SeeOther('/303');
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(303, $res->getStatusCode());
        $this->assertEquals('/303', $res->headers->get('Location'));
    }

    public function test307()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new TemporaryRedirect('/307');
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(307, $res->getStatusCode());
        $this->assertEquals('/307', $res->headers->get('Location'));
    }

    public function test401()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new Unauthorized();
        });
        $r->error(401, function () {
            return 'Unauthorized';
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function test403()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new Forbidden();
        });
        $r->error(403, function () {
            return 'Forbidden';
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function test404()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new NotFound();
        });
        $r->error(404, function () {
            return 'NotFound';
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function test413()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new RequestEntityTooLarge();
        });
        $r->error(413, function () {
            return 'RequestEntityTooLarge';
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(413, $res->getStatusCode());
    }

    public function test422()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new UnprocessableEntity();
        });
        $r->error(422, function () {
            return 'UnprocessableEntity';
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(422, $res->getStatusCode());
    }

    public function test503()
    {
        $r = new Router();
        $r->get('/', function () {
            throw new ServiceUnavailable();
        });
        $r->error(503, function () {
            return 'RequestEntityTooLarge';
        });
        $res = $r->run(Request::create('/'));
        $this->assertEquals(503, $res->getStatusCode());
    }
}
