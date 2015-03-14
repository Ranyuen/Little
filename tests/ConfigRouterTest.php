<?php

use Ranyuen\Little\Plugin\ConfigRouter;
use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class ConfigRouterTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        $prop = new \ReflectionProperty('Ranyuen\Little\Router', 'plugins');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function testRouteByConfig()
    {
        Router::plugin('Ranyuen\Little\Plugin\ConfigRouter');
        $r = new Router();
        $r->routeByConfig([
            'map' => [
                ['/', function () { return 'index'; }],
            ],
            'error' => [
                500 => function () { return 'index 500'; },
            ],
            'group' => [
                '/blog' => [
                    'map' => [
                        [
                            '/:page',
                            function ($page) { return "blog index $page"; },
                            'assert' => ['page' => '/\A\d+\z/'],
                            'name'   => 'blog_index',
                        ],
                        ['/show/:id', function (Router $r, Request $req) { return $r->error(404, $req); }],
                    ],
                    'error' => [
                        404 => function () { return 'blog 404'; },
                    ],
                ],
            ],
        ]);

        $req = Request::create('/');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('index', $res->getContent());

        $req = Request::create('/');
        $res = $r->error(500, $req);
        $this->assertEquals(500, $res->getStatusCode());
        $this->assertEquals('index 500', $res->getContent());

        $req = Request::create('/blog/2');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('blog index 2', $res->getContent());

        $req = Request::create('/blog/');
        $req->query->set('page', 1);
        $res = $r->run('blog_index', $req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('blog index 1', $res->getContent());

        $req = Request::create('/blog/show/mOmonga');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());
        $this->assertEquals('blog 404', $res->getContent());
    }
}
