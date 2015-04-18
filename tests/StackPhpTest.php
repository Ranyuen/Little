<?php

require 'Fixture/StackMiddlewares.php';

use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class StackPhpTest extends PHPUnit_Framework_TestCase
{
    public function testStack()
    {
        $r = new Router();
        $r->stack('Fixture\FirstMiddleware');
        $r->stack('Fixture\SecondMiddleware');
        $r->get('/', function ($req) { return $req->query->get('content', '').' /'; });
        $r->group('/g', function ($r) {
            $r->stack('Fixture\ThirdMiddleware');
            $r->get('/', function ($req) { return $req->query->get('content', '').' /g/'; });
        });

        $res = $r->run(Request::create('/'));
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals(' b1 b2 / a2 a1', $res->getContent());

        $res = $r->run(Request::create('/g/'));
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals(' b1 b2 b3 /g/ a3 a2 a1', $res->getContent());
    }
}
