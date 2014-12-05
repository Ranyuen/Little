Ranyuen/Little
==
Request-Route-Ракушки-Response

No model, no view, no controller, no MVC.<br/>
No ORM. No template.<br/>
No convention, no configuration.<br/>
No framwork. No WAF.

**Ranyuen/Little** is a simple & scalable kernel to constract all size of Web applications. Based on [Ranyuen/Di](https://github.com/Ranyuen/Di) & Symfony Components.

_cf._ [codeguy/Slim](http://www.slimframework.com/)

_cf._ [silexphp/Silex](http://silex.sensiolabs.org/)

_cf._ [Stack](http://stackphp.com/)

Features
--
1. Simple routing API like every micro WAFs.
2. DI/AOP support with Ranyuen/Di.
3. Pluggable routing logic.
4. Stack middleware.

Example
--
See _tests/_ directory.




<!--
Auto routing by annotation.
```php
<?php
class UserController
{
    /** @Route('GET /user/{id}') */
    public function show($id)
    {
        return new Response("User of $id");
    }

    /** @Route('POST /user/create') */
    public function create()
    {
        return new Response('ok');
    }
}

$r = new Router;
$r->route('UserController');

$res = $r->run($req);
```

Grouping.
```php
<?php
class UserController
{
    /** @Route('GET /{id}') */
    public function show($id)
    {
        return new Response("User of $id");
    }

    /** @Route('POST /create') */
    public function create()
    {
        return new Response('ok');
    }
}

$r = new Router;
$r->group('/user', function ($r) {
    $r->route('UserController');
});

$res = $r->run($req);
```

Group by annotation.
```php
<?php
/** @Route('/user') */
class UserController
{
    /** @Route('GET /{id}') */
    public function show($id)
    {
        return new Response("User of $id");
    }

    /** @Route('POST /create') */
    public function create()
    {
        return new Response('ok');
    }
}

$r = new Router;
$r->route('UserController');

$res = $r->run($req);
```

Route all controllers under the namespace.
```php
namespace Controller;

class IndexController
{
    /**
     * @Route('GET / hello')
     *
     * Equals to
     *     $router->get('/', 'Controller\IndexController::index')
     *         ->name('hello');
     */
    public function index(Router $router)
    {
        return new Response('Hello');
    }
}

$r = new Router;
$r->route('Controller');

$res = $r->run($req);
```

Use DI and AOP.
```php
class UserController
{
    /** @Inject */
    private $req;

    /**
     * @Wrap('auth,logging')
     * @Route('GET /user/{id}')
     */
    public function show($id, Request $q)
    {
       $q === $this->req;
    }
}

$r = Router;
$c = new \Ranyuen\Di\Container;
$c['router'] = $r;
$c['auth'] = $c->protect(function ($invocation, $args) use ($c) {
    list($id, $req) = $args;
    if (!isAuth($req)) {
        return $c['router']->error(403, $req);
    }
    return $invocation($id, $req);
});
$c['logging'] = $c->protect(function ($invocation, $args) {
    list($id, $req) = $args;
    $res = $invocation($id, $req);
    logging($req, $res);
    return $res;
});
$r->setContainer($c);
$r->route('UserController');

$res = $r->run($req);
```

Stack middleware
-->
