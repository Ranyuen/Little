[![Build Status](https://travis-ci.org/Ranyuen/Little.svg)](https://travis-ci.org/Ranyuen/Little)
[![HHVM Status](http://hhvm.h4cc.de/badge/ranyuen/little.svg)](http://hhvm.h4cc.de/package/ranyuen/little)

# Ranyuen/Little

Request-Route-Ракушки-Response

No model, no view, no controller, no MVC.<br/>
No ORM. No template.<br/>
No convention, no configuration.<br/>
No middleware. No framwork. No WAF.

**Ranyuen/Little** is a simple & scalable kernel to constract all size of Web applications. Based on [Ranyuen/Di](https://github.com/Ranyuen/Di) & Symfony Components.

_cf._ [codeguy/Slim](http://www.slimframework.com/)

_cf._ [silexphp/Silex](http://silex.sensiolabs.org/)

## Features

1. Simple routing API like every micro WAFs.
2. DI/AOP support with Ranyuen/Di.
3. Pluggable routing logic.

## Install

```sh
composer require ranyuen/little
```

Support PHP >=7.4.

## Example

For more details please see _tests/_ directory.

For small Web pages.

```php
<?php
require 'vendor/autoload.php';

use Ranyuen\Little\Router;
use Ranyuen\Little\Request;

$r = new Router();

$r->get('/', function () {
    return 'Hello.';
});

$r->error(404, function () {
  return 'Not Found';
});

$r->group('/blog', function ($r) {
    $r->get('/:year/:month/:date', function ($year, $month, $date) {
        return "View articles at $year-$month-$date.";
    })
        ->assert('year', '/\d{4}/')
        ->assert('month', '/\d{1,2}/')
        ->assert('date', '/\d{1,2}/');

    $r->get('/category', function ($name = 'all') {
        return "List articles in $name.";
    });

    $r->error(404, function ($req) {
        return "{$req->getPathInfo()} is not found.";
    });
});

$r->run(Request::createFromGlobals())->send();
```

For more large pages we can use controller class.

```php
<?php
require 'vendor/autoload.php';

use Ranyuen\Little\Router;
use Ranyuen\Little\Request;

class IndexController {
    public function index() {
        return 'Hello.';
    }

    public function notFound() {
        return 'Not Found';
    }
}

class BlogController {
    public function show($year, $month, $date) {
        return "View articles at $year-$month-$date.";
    }

    public function category($name = 'all') {
        return "List articles in $name.";
    }

    public function notFound($req) {
        return "{$req->getPathInfo()} is not found.";
    }
}

$r = new Router();
$r->get('/', 'IndexController@index');
$r->error(404, 'IndexController@notFound');
$r->group('/blog', function ($r) {
    $r->get('/:year/:month/:date', 'BlogController@show')
        ->assert('year', '/\d{4}/')
        ->assert('month', '/\d{1,2}/')
        ->assert('date', '/\d{1,2}/');
    $r->get('/category', 'BlogController@category');
    $r->error(404, 'BlogController@notFound');
});

$r->run(Request::createFromGlobals())->send();
```

For complex pages config or annotations are useful.

```php
<?php
require 'vendor/autoload.php';

use Ranyuen\Di\Container;
use Ranyuen\Little\Router;
use Ranyuen\Little\Request;
use Ranyuen\Little\Response;

class IndexController {
    /** @Route('/') */
    public function index() {
        return 'Hello.';
    }

    /** @Route(error=404) */
    public function notFound() {
        return 'Not Found';
    }
}

/** @Route('/blog') */
class BlogController {
    /**
     * @Inject
     * @var PDO
     */
    private $db;
    /** @Inject */
    private $view;
    /** @Inject */
    private $auth;

    /** @Route('/:year/:month/:date',assert={year:'/\d{4}/',month:'/\d{1,2}/',date:'/\d{1,2}/'}) */
    public function show($year, $month, $date) {
        return "View articles at $year-$month-$date.";
    }

    /** @Route('/category') */
    public function category($name = 'all') {
        return "List articles in $name.";
    }

    /**
     * @Route('/edit/:id?')
     * @Wrap('auth')
     */
    public function edit(Request $req, $id = 0) {
        return $this->view->render('blog/edit');
    }

    /**
     * @Route('/save/:id',via=POST)
     * @Wrap('auth')
     */
    public function save(Request $req, $id, $title, $content) {
        $id = (int)$id;
        if (0 === $id) {
            $statement = $this->db->prepare('INSERT INTO blog(title, content) VALUES (:title, :content)');
        } else {
            $statement = $this->db->prepare('UPDATE blog SET title = :title, content = :content WHERE id = :id');
            $statement->bindParam('id', $id);
        }
        $statement->bindParam('title', $title);
        $statement->bindParam('title', $content);
        $statement->execute();
        return new Response('', 303, ['Location' => '/blog/']);
    }

    /**
     * @Route('/save/:id',via=DELETE)
     * @Wrap('auth')
     */
    public function destroy(Request $req, $id) {
        $statement = $this->db->prepare('DELETE blog WHERE id = :id');
        $statement->bindParam('id', $id);
        $statement->execute();
        return new Response('', 303, ['Location' => '/blog/']);
    }

    /** @Route(error=403) */
    public function forbidden() {
        return new Response('', 403);
    }

    /** @Route(error=404) */
    public function notFound(Request $req) {
        return "{$req->getPathInfo()} is not found.";
    }
}

$c = new Container();
$c['db'] = function (Container $c) {
    return new \PDO('mysql:host=localhost;dbname=rrrr;charset=utf8', 'user', 'password');
};
$c['view'] = function (Container $c) {
    // Some template engine.
};
$c['auth'] = $c->protect(function ($invocation, $args) {
    $req = $args[0];
    if (fail($req)) { // Some authentication logic.
        throw new \Ranyuen\Little\Exception\Forbidden();
    }
    return call_user_func_array($invocation, $args);
});

Router::plugin('Ranyuen\Little\Plugin\ControllerAnnotationRouter');
$r = new Router($c);
$r->registerController('IndexController');
$r->registerController('BlogController');

$req = Request::createFromGlobals();
$res = $r->run($req);
$res->send();
```
