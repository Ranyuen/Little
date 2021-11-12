<?php

namespace Fixture;

use Ranyuen\Little\Router;
use Ranyuen\Little\Request;

class MomongaController
{
    /** @Inject */
    private $req;
    /** @Inject */
    private $request;
    /**
     * @var Request
     * @Inject
     */
    private $q;
    /** @Inject */
    private $router;
    /**
     * @var Router
     * @Inject
     */
    private $r;
    /** @Inject */
    private $momonga;
    /**
     * @var Fixture\Momonga
     * @Inject
     */
    private $m;
    /** @Inject */
    private $test;
    /** @var array */
    private $args;

    public function __construct($momonga = null, Momonga $m = null)
    {
        $this->args = [
            'momonga' => $momonga,
            'm'       => $m,
        ];
    }

    public function index()
    {
        return 'Momonga index';
    }

    public function ditest()
    {
        $this->test->assertInstanceOf('Fixture\Momonga', $this->args['momonga']);
        $this->test->assertInstanceOf('Fixture\Momonga', $this->args['m']);
        $this->test->assertInstanceOf('Fixture\Momonga', $this->momonga);
        $this->test->assertInstanceOf('Fixture\Momonga', $this->m);

        return 'Momonga ditest';
    }

    public function error500($ex)
    {
        return (string) $ex;
    }

    public function argtest($test, $req, $request, Request $q, $router, Router $r, $momonga, Momonga $m)
    {
        $test->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $req);
        $test->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request);
        $test->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $q);
        $test->assertInstanceOf('Ranyuen\Little\Router', $router);
        $test->assertInstanceOf('Ranyuen\Little\Router', $r);
        $test->assertInstanceOf('Fixture\Momonga', $momonga);
        $test->assertInstanceOf('Fixture\Momonga', $m);

        return 'Momonga argtest';
    }
}
