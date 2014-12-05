<?php
require_once 'tests/Fixture/Momonga.php';
require_once 'tests/Fixture/MomongaController.php';

use Fixture\Momonga;
use Ranyuen\Di\Container;
use Ranyuen\Little\Router;
use Symfony\Component\HttpFoundation\Request;

class ControllerRouteTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testRouteToController()
    {
        $r = new Router();
        $r->get('/', 'Fixture\MomongaController@index');
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Momonga index', $res->getContent());
    }

    public function testErrorToController()
    {
        $r = new Router();
        $r->error(500, 'Fixture\MomongaController@error500');
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $ex = new \Exception('Some Error');
        $res = $r->error(500, $req, $ex);
        $this->assertEquals(500, $res->getStatusCode());
        $this->assertEquals((string) $ex, $res->getContent());
    }

    /**
     */
    public function testControllerDi()
    {
        $c = new Container(['test' => $this]);
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Momonga();
        });
        $r = new Router($c);
        $r->get('/', 'Fixture\MomongaController@ditest');
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Momonga ditest', $res->getContent());
    }

    /**
     */
    public function testControllerArgs()
    {
        $c = new Container(['test' => $this]);
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Momonga();
        });
        $r = new Router($c);
        $r->get('/', 'Fixture\MomongaController@argtest');
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Momonga argtest', $res->getContent());
    }
}
