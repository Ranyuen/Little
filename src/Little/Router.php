<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Little;

use Ranyuen\Di\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 */
class Router implements HttpKernelInterface
{
    /** @var self */
    private $parent;
    /** @var array [string $path => self $router] */
    private $childs = [];
    /** @var Route[] */
    private $routes = [];
    /** @var array */
    private $errorRoutes = [];
    /** @var Route */
    private $currentMap;
    /** @var Container */
    private $container;

    /**
     * @param Container $container
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(Container $container = null)
    {
        Request::enableHttpMethodParameterOverride();
        $this->container = $container ? $container : new Container();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $controller
     *
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string          $path
     * @param callback|string $controller
     *
     * @return this|Response
     */
    public function map($path, $controller)
    {
        $this->currentMap = new Route($this);
        $this->currentMap->setPath($path);
        $this->currentMap->setController($controller);
        array_unshift($this->routes, $this->currentMap);

        return $this;
    }

    /**
     * @return this
     */
    public function via()
    {
        if (!$this->currentMap) {
            return $this;
        }
        $methods = array_map(
            function ($method) {
                return strtoupper($method);
            },
            func_get_args()
        );
        $this->currentMap->addMethods($methods);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return this
     */
    public function name($name)
    {
        if (!$this->currentMap) {
            return $this;
        }
        $this->currentMap->name = $name;

        return $this;
    }

    /**
     * @param string        $path
     * @param self|callback $router
     *
     * @return this
     */
    public function group($path, $router)
    {
        if (!($router instanceof self)) {
            $child = new self();
            $router($child);

            return $this->group($path, $child);
        }
        $this->childs[$path] = $router;
        $router->registerParent($path, $this);

        return $this;
    }

    /**
     */
    public function registerParent($path, Router $router)
    {
        $this->parent = $router;
        foreach ($this->routes as $route) {
            $route->setPath($path.$route->path);
        }
    }

    /**
     * Run request handler.
     *
     * Run metched handler.
     *     run(Request)
     * Assign specific route. Ignores which dose the request matches or not.
     *     run(string $name, Request)
     *
     * @return Response
     */
    public function run()
    {
        if (func_get_arg(0) instanceof Request) {
            $name = null;
            $req = func_get_arg(0);
        } else {
            list($name, $req) = func_get_args();
        }
        if ($res = $this->runAsAlone($req, $name)) {
            return $res;
        }
        foreach ($this->childs as $path => $router) {
            if ($res = $router->runAsAlone($req, $name)) {
                return $res;
            }
        }

        return $this->error(404, $req);
    }

    /**
     */
    public function runAsAlone(Request $req, $name = null)
    {
        if (!$name) {
            foreach ($this->routes as $route) {
                if ($res = $route->matchOrRun($req)) {
                    if ($res instanceof \Exception) {
                        return $this->error(500, $req, $res);
                    }

                    return $res;
                }
            }
        } else {
            foreach ($this->routes as $route) {
                if ($route->name === $name) {
                    $res = $route->run($req);
                    if ($res instanceof \Exception) {
                        return $this->error(500, $req, $res);
                    }

                    return $res;
                }
            }
        }

        return;
    }

    /**
     * Set or run error handler.
     *
     * Set handler.
     *     error(int 404, callable)
     * Run handler.
     *     error(int 404, Request)
     *     error(int 500, Request, Exception)
     *
     * @param int                     $status
     * @param callable|string|Request $controller
     * @param Exception               $ex
     *
     * @return this|Response
     */
    public function error($status, $controller = null, $ex = null)
    {
        if ($controller instanceof Request) {
            $req = $controller;
            unset($controller);
            if ($res = $this->runErrorAsAlone($status, $req, $ex)) {
                return $res;
            }
            if ($this->parent
                && $res = $this->parent->runErrorAsAlone($status, $req, $ex)) {
                return $res;
            }

            return new Response((string) $ex, $status);
        }
        $this->currentMap = null;
        $currentMap = new Route($this);
        $currentMap->setController($controller);
        $this->errorRoutes[$status] = $currentMap;

        return $this;
    }

    /**
     */
    public function runErrorAsAlone($status, Request $req, \Exception $ex = null)
    {
        if (!isset($this->errorRoutes[$status])) {
            return;
        }
        $res = $this->errorRoutes[$status]->run(
            $req,
            [
                'e'         => $ex,
                'ex'        => $ex,
                'err'       => $ex,
                'error'     => $ex,
                'exception' => $ex,
                'Exception' => $ex,
            ]
        );
        if ($res instanceof \Exception) {
            if (500 === $status) {
                return new Response((string) $ex, 500);
            }

            return $this->error(500, $req, $ex);
        }
        $res->setStatusCode($status);

        return $res;
    }

    /**
     * @param string $class
     *
     * @return this
     */
    public function route($class)
    {
        $class = new \ReflectionClass($class);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $annotation = new RouteAnnotation();
        $group = $annotation->getGroup($class);
        if ($group) {
            $router = new self();
        } else {
            $router = $this;
        }
        foreach ($methods as $method) {
            foreach ($annotation->getRoutes($method) as $route) {
                list($method, $path) = $route;
                $router->map(
                    $path,
                    "{$class->getName()}::{$method->getName()}"
                )->via($method);
            }
        }
        if ($group) {
            $this->group($group, $router);
        }

        return $this;
    }

    /** {@inheritdoc} */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
    }

    /**
     * Alias of map() & via('GET').
     *
     * @return this
     */
    public function get($path, $controller)
    {
        return $this->map($path, $controller)->via('GET');
    }

    /**
     * Alias of map() & via('POST').
     *
     * @return this
     */
    public function post($path, $controller)
    {
        return $this->map($path, $controller)->via('POST');
    }

    /**
     * Alias of map() & via('PUT').
     *
     * @return this
     */
    public function put($path, $controller)
    {
        return $this->map($path, $controller)->via('PUT');
    }

    /**
     * Alias of map() & via('DELETE').
     *
     * @return this
     */
    public function delete($path, $controller)
    {
        return $this->map($path, $controller)->via('DELETE');
    }

    /**
     * Alias of map() & via('OPTIONS').
     *
     * @return this
     */
    public function options($path, $controller)
    {
        return $this->map($path, $controller)->via('OPTIONS');
    }

    /**
     * Alias of map() & via('PATCH').
     *
     * @return this
     */
    public function patch($path, $controller)
    {
        return $this->map($path, $controller)->via('PATCH');
    }
}
