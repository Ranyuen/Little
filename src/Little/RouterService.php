<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Little;

use Ranyuen\Di\Container;
use Ranyuen\Little\Injector\ContainerSet;
use Ranyuen\Little\Injector\FunctionInjector;

/**
 * Router service.
 */
class RouterService
{
    /** @var Router */
    private $facade;
    /** @var Router */
    private $parent;
    /** @var Router[] */
    private $childs = [];
    /** @var Container */
    private $c;
    /** @var Route[] */
    private $routes = [];
    /** @var array */
    private $namedRoutes = [];
    /** @var array [error status=>controller] */
    private $errorHandlers = [];

    public function __construct(Router $facade, Container $c)
    {
        Request::enableHttpMethodParameterOverride();
        $this->facade = $facade;
        $this->c      = $c;
    }

    public function addRoute(Route $route)
    {
        if (in_array($route, $this->routes, true)) {
            return;
        }
        $this->routes[] = $route;
    }

    public function registerNamedRoute($name, Route $route)
    {
        $this->namedRoutes[$name] = $route;
        $this->addRoute($route);
    }

    public function addErrorHandler($status, $controller)
    {
        $this->errorHandlers[$status] = $controller;
    }

    public function addGroup($path, Router $router)
    {
        if (in_array($router, $this->childs)) {
            return;
        }
        $this->childs[$path] = $router;
        $router->registerParent($path, $this->facade);
    }

    public function registerParent($path, Router $router)
    {
        $this->parent = $router;
    }

    /**
     * @param string  $name
     * @param Request $req
     *
     * @return Response
     */
    public function run($name, Request $req)
    {
        if ($route = $this->findMatchedRoute($name, $req)) {
            try {
                $res = $route->response(
                    [
                        'req'                                      => $req,
                        'request'                                  => $req,
                        'Ranyuen\Little\Request'                   => $req,
                        'Symfony\Component\HttpFoundation\Request' => $req,
                        'router'                                   => $this->facade,
                        'Ranyuen\Little\Router'                    => $this->facade,
                    ]
                );
            } catch (\Exception $ex) {
                return $route->runError(500, $ex);
            }
            $res = $this->toResponse($res);
            if ('HEAD' === $req->getMethod()) {
                $res->setContent('');
            }

            return $res;
        }

        return $this->runError(404, $req);
    }

    /**
     * @param string  $name
     * @param Request $req
     * @param string  $prefix
     *
     * @return RequestedRoute|null
     */
    public function findMatchedRoute($name, Request $req, $prefix = '')
    {
        if ($name) {
            if (isset($this->namedRoutes[$name])) {
                return new RequestedRoute($this->facade, $this->namedRoutes[$name], $req, []);
            }
        } else {
            foreach ($this->routes as $route) {
                if ($route = $route->matchRequest($req, $prefix)) {
                    return $route;
                }
            }
        }
        foreach ($this->childs as $path => $child) {
            if ($route = $child->findMatchedRoute($name, $req, $prefix.$path)) {
                return $route;
            }
        }
    }

    /**
     * @param int        $status
     * @param Request    $req
     * @param \Exception $ex
     *
     * @return Response
     */
    public function runError($status, Request $req, \Exception $ex = null)
    {
        if (!($handler = $this->findErrorHandler($status))) {
            return new Response((string) $ex, $status);
        }
        $set = new ContainerSet();
        $set->addContainer($this->c);
        $set->addRequest($req);
        $set->addArray(
                [
                    'e'         => $ex,
                    'ex'        => $ex,
                    'err'       => $ex,
                    'error'     => $ex,
                    'exception' => $ex,
                    'Exception' => $ex,
                ]
            );
        $injector = new FunctionInjector($set);
        try {
            $res = $injector->registerFunc($handler)
                ->invoke();
        } catch (\Exception $ex2) {
            if (500 === $status) {
                return new Response((string) $ex2, 500);
            }

            return $this->runError(500, (string) $ex2);
        }
        if ($res instanceof \Exception) {
            if (500 === $status) {
                return new Response((string) $ex, 500);
            }

            return $this->runError(500, (string) $ex);
        }
        $res = $this->toResponse($res);
        $res->setStatusCode($status);

        return $res;
    }

    /**
     * @param int $status
     *
     * @return mixed|null
     */
    public function findErrorHandler($status)
    {
        if (isset($this->errorHandlers[$status])) {
            return $this->errorHandlers[$status];
        }
        if (!$this->parent) {
            return;
        }

        return $this->parent->findErrorHandler($status);
    }

    private function toResponse($obj)
    {
        if ($obj instanceof Response) {
            return $obj;
        }
        if (is_int($obj)) {
            return new Response('', $obj);
        }

        return new Response((string) $obj, 200);
    }
}
