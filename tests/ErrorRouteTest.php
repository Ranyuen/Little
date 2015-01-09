<?php

use Ranyuen\Little\Request;
use Ranyuen\Little\Response;
use Ranyuen\Little\Router;

class ErrorRouteTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testBasicError()
    {
        $r = new Router();
        $r->error(404, function () {
            return new Response('Not Found', 404);
        });
        $r->error(500, function ($ex) {
            return new Response((string) $ex, 500);
        });
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->error(404, $req);
        $this->assertEquals(404, $res->getStatusCode());
        $this->assertEquals('Not Found', $res->getContent());
        $ex = new Exception('Some Error');
        $res = $r->error(500, $req, $ex);
        $this->assertEquals(500, $res->getStatusCode());
        $this->assertEquals((string) $ex, $res->getContent());
    }

    /**
     */
    public function testControllerErrorArgs()
    {
        $test = $this;
        $someError = new Exception('Some Error');
        $r = new Router();
        $r->error(
            500,
            function ($e, $ex, $err, $error, $exception, Exception $var) use ($test, $someError) {
                $test->assertSame($someError, $e);
                $test->assertSame($someError, $ex);
                $test->assertSame($someError, $err);
                $test->assertSame($someError, $error);
                $test->assertSame($someError, $exception);
                $test->assertSame($someError, $var);

                return new Response('', 500);
            }
        );
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->error(500, $req, $someError);
        $this->assertEquals(500, $res->getStatusCode());
    }

    /**
     */
    public function testNotFound()
    {
        $r = new Router();
        $r->error(404, function () { return 'Not Found'; });
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );
        $res = $r->run($req);
        $this->assertEquals(404, $res->getStatusCode());
        $this->assertEquals('Not Found', $res->getContent());
    }

    /**
     */
    public function testInternalServerError()
    {
        $test = $this;
        $someError = new Exception();
        $r = new Router();
        $r->get('/raise', function () use ($someError) {
            throw $someError;
        });
        $r->error(500, function ($ex) use ($test, $someError) {
            $test->assertSame($someError, $ex);

            return 'Internal Server Error';
        });
        $req = new Request(
            [],
            [],
            [], [], [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/raise']
        );
        $res = $r->run($req);
        $this->assertEquals(500, $res->getStatusCode());
        $this->assertEquals('Internal Server Error', $res->getContent());
    }
}
