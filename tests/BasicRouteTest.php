<?php

use Ranyuen\Little\Request;
use Ranyuen\Little\Response;
use Ranyuen\Little\Router;

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

        $req = Request::create('/basic');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('basic', $res->getContent());

        $req = Request::create('/nonbasic');
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
        $req = Request::create('/basic');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('GET', $res->getContent());
        $req = Request::create('/basic', 'HEAD');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('', $res->getContent());
        $req = Request::create('/basic', 'POST');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('POST', $res->getContent());
        $req = Request::create('/basic', 'PUT');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('PUT', $res->getContent());
        $req = Request::create('/basic', 'DELETE');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('DELETE', $res->getContent());
        $req = Request::create('/basic', 'OPTIONS');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('OPTIONS', $res->getContent());
        $req = Request::create('/basic', 'PATCH');
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

        $req = Request::create('/');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = Request::create('/', 'POST');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('via', $res->getContent());

        $req = Request::create('/', 'PUT');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('via', $res->getContent());

        $req = Request::create('/', 'DELETE');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = Request::create('/', 'OPTIONS');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = Request::create('/', 'PATCH');
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
        $req = Request::create('/noname');

        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $r->run('named', $req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('named', $res->getContent());

        $r = new Router();
        $r->get('/named/:id', function ($id) { return new Response("named $id"); })
            ->name('named');
        $req = Request::create('/noname');
        $req->query->set('id', 42);
        $res = $r->run('named', $req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('named 42', $res->getContent());
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

        $req = Request::create('/override', 'POST');
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());

        $req = Request::create('/override', 'POST', [], [], [], ['HTTP_X-HTTP-Method-Override' => 'PATCH']);
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('PATCH', $res->getContent());

        $req = Request::create('/override', 'POST', ['_method' => 'PATCH']);
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
     * |Params in URL.      |none.     |$matches                         |
     * |Each request params.|none.     |Param name.                      |
     */
    public function testControllerArgs()
    {
        $test = $this;
        $r = new Router();
        $r->map('/args', function ($req, $request, Request $q, $router, Router $r) use ($test) {
            $test->assertInstanceOf('Ranyuen\Little\Request', $req);
            $test->assertInstanceOf('Ranyuen\Little\Request', $request);
            $test->assertInstanceOf('Ranyuen\Little\Request', $q);
            $test->assertInstanceOf('Ranyuen\Little\Router', $router);
            $test->assertInstanceOf('Ranyuen\Little\Router', $r);
            $test->assertEquals('mOmonga', $req->get('name'));
            $test->assertEquals('mOmonga', $request->get('name'));
            $test->assertEquals('mOmonga', $q->get('name'));

            return new Response('args');
        })->via('GET', 'POST');

        $req = Request::create('/args?name=mOmonga');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('args', $res->getContent());

        $req = Request::create('/args', 'POST', ['name' => 'mOmonga']);
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('args', $res->getContent());
    }

    /**
     * Sinatra-like routing format.
     */
    public function testRegexRoutePath()
    {
        $test = $this;

        $r = new Router();
        $r->get('#/first/(.+?)/second/(?<third>.+)#', function ($matches, $third) use ($test) {
            $test->assertEquals('momonga', $matches[1]);
            $test->assertEquals('momonga/momonga', $matches[2]);
            $test->assertEquals('momonga/momonga', $third);
        });
        $req = Request::create('/first/momonga/second/momonga/momonga');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());

        $r = new Router();
        $r->get('/first/*/second/*', function ($matches) use ($test) {
            $test->assertEquals('momonga', $matches[1]);
            $test->assertEquals('momonga/momonga', $matches[2]);
        });
        $req = Request::create('/first/momonga/second/momonga/momonga');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
    }

    /**
     * Controller callback can get URL, GET & POST params through its args.
     */
    public function testRequestAndRouteParam()
    {
        $r = new Router();
        $r->put('/basic/:id', function ($id, $name) {
            return new Response("$name $id");
        });
        $req = Request::create('/basic/42', 'PUT', ['id' => 41, 'name' => 'mOmonga']);
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
        $req = Request::create('/basic', 'DELETE');
        $res = $r->run($req);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $res);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('mOmonga', $res->getContent());

        $r = new Router();
        $r->delete('/basic', function () { return 404; });
        $req = Request::create('/basic', 'DELETE');
        $res = $r->run($req);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $res);
        $this->assertEquals(404, $res->getStatusCode());
    }
}
