<?php

use Ranyuen\Little\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicRouteTest extends PHPUnit_Framework_TestCase
{
    /**
     * Basic micro routing.
     *
     * @example
     *     $r = new Router;
     *     $r->get('/user/{id}', function ($id) {
     *         return new Response("User of $id");
     *     });
     *     $r->run(Request::createFromGlobals())->send();
     */
    public function testBasicRoute()
    {
        $r = new Router();
        $r->get('/basic', function () { return new Response('basic'); });

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('basic', $res->getContent());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/nonbasic']
        );
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());
    }

    /**
     * We can map GET, POST, PUT, DELETE, OPTIONS and PATCH. HEAD calls GET.
     */
    public function testRouteMethod()
    {
        $r = new Router();
        $r->get('/basic', function () { return new Response('GET'); });
        $r->post('/basic', function () { return new Response('POST'); });
        $r->put('/basic', function () { return new Response('PUT'); });
        $r->delete('/basic', function () { return new Response('DELETE'); });
        $r->options('/basic', function () { return new Response('OPTIONS'); });
        $r->patch('/basic', function () { return new Response('PATCH'); });
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('GET', $res->getContent());
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'HEAD', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('', $res->getContent());
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('POST', $res->getContent());
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'PUT', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('PUT', $res->getContent());
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'DELETE', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('DELETE', $res->getContent());
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'OPTIONS', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('OPTIONS', $res->getContent());
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'PATCH', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('PATCH', $res->getContent());
    }

    /**
     * We can match multiple method by map() and via().
     */
    public function testVia()
    {
        $r = new Router();
        $r->map('/', function () { return new Response('via'); })
            ->via('POST', 'PUT', 'PATCH');

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('via', $res->getContent());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'PUT', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('via', $res->getContent());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'DELETE', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'OPTIONS', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'PATCH', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('via', $res->getContent());
    }

    /**
     * Bind name to route.
     */
    public function testNamedRoute()
    {
        $r = new Router();
        $r->get('/named', function () { return new Response('named'); })
            ->name('named');
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/noname']
        );

        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $r->run('named', $req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('named', $res->getContent());
    }

    /**
     * X-HTTP-Method-Override is available.
     *
     * In HTTP header.
     *     X-HTTP-Method-Override: PUT
     * In HTML form.
     *     <input name="_method" value="PUT" type="hidden"/>
     */
    public function testMethodOverride()
    {
        $r = new Router();
        $r->patch('/override', function () { return new Response('PATCH'); });

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/override']
        );
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/override', 'HTTP_X-HTTP-Method-Override' => 'PATCH']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('PATCH', $res->getContent());

        $req = new Request(
            [],
            ['_method' => 'PATCH'],
            [], [], [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/override']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('PATCH', $res->getContent());
    }

    /**
     * Variables passed to controllers are detected from both Type hint and var name.
     *
     * |Passed variable     |Type hint |Variable name                    |
     * |--------------------|----------|---------------------------------|
     * |Current request.    |Request   |$req, $request                   |
     * |Caused exception.   |\Exception|$e, $ex, $exception, $err, $error|
     * |Binding router.     |Router    |$router                          |
     * |Each request params.|none.     |Param name.                      |
     */
    public function testControllerArgs()
    {
        $test = $this;
        $r = new Router();
        $r->map('/args', function ($req, $request, Request $q, $router, Router $r) use ($test) {
            $test->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $req);
            $test->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request);
            $test->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $q);
            $test->assertInstanceOf('Ranyuen\Little\Router', $router);
            $test->assertInstanceOf('Ranyuen\Little\Router', $r);
            $test->assertEquals('mOmonga', $req->get('name'));
            $test->assertEquals('mOmonga', $request->get('name'));
            $test->assertEquals('mOmonga', $q->get('name'));

            return new Response('args');
        })->via('GET', 'POST');

        $req = new Request(
            ['name' => 'mOmonga'],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/args']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('args', $res->getContent());

        $req = new Request(
            [],
            ['name' => 'mOmonga'],
            [], [], [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/args']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('args', $res->getContent());
    }

    /**
     * Controller callback can get URL, GET & POST params through its args.
     */
    public function testRequestAndRouteParam()
    {
        $r = new Router();
        $r->put('/basic/{id}', function ($id, $name) {
            return new Response("$name $id");
        });
        $req = new Request(
            [],
            ['id' => 41, 'name' => 'mOmonga'],
            [], [], [],
            ['REQUEST_METHOD' => 'PUT', 'REQUEST_URI' => '/basic/42']
        );
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('mOmonga 42', $res->getContent());
    }

    /**
     * When the controller returns string, we create a response that has a body of the string.
     */
    public function testAutoResponse()
    {
        $r = new Router();
        $r->delete('/basic', function () { return 'mOmonga'; });
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'DELETE', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $res);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('mOmonga', $res->getContent());

        $r = new Router();
        $r->delete('/basic', function () { return 404; });
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'DELETE', 'REQUEST_URI' => '/basic']
        );
        $res = $r->run($req);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $res);
        $this->assertEquals(404, $res->getStatusCode());
    }
}
