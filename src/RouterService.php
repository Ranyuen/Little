<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Little
 */
namespace Ranyuen\Little;

use Ranyuen\Di\Container;
use Ranyuen\Di\Dispatcher\Dispatcher;

/**
 * Router service.
 */
class RouterService
{
    /**
     * Error handlers.
     *
     * @var array [error status=>controller]
     */
    public $errorHandlers = [];

    /**
     * Facade.
     *
     * @var Router
     */
    private $facade;
    /**
     * DI container.
     *
     * @var Container
     */
    private $c;
    /**
     * Parent router.
     *
     * @var Router?
     */
    private $parent;
    /**
     * Routes.
     *
     * @var (Route|Router)[]
     */
    private $routes = [];
    /**
     * Routes has a name.
     *
     * @var array [string $name=>Route]
     */
    private $namedRoutes = [];

    /**
     * Constructor.
     *
     * @param Router    $facade Router facade.
     * @param Container $c      DI container.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(Router $facade, Container $c)
    {
        Request::enableHttpMethodParameterOverride();
        $this->facade = $facade;
        $this->c      = $c;
    }

    /**
     * Add a route.
     *
     * @param Route  $route Route.
     * @param string $name  Optional route name.
     *
     * @return void
     */
    public function addRoute(Route $route, $name = null)
    {
        if ($name) {
            $this->namedRoutes[$name] = $route;
        }
        if (in_array($route, $this->routes, true)) {
            return;
        }
        $this->routes[] = $route;
    }

    /**
     * Add a child router.
     *
     * @param string $path   Path DSL.
     * @param Router $router Child router.
     *
     * @return void
     */
    public function addGroup($path, Router $router)
    {
        if (in_array([$path, $router], $this->routes, true)) {
            return;
        }
        $this->routes[] = [$path, $router];
        $router->registerParent($this->facade);
    }

    /**
     * Set the router as a parent.
     *
     * @param Router $router Parent router.
     *
     * @return void
     */
    public function registerParent(Router $router)
    {
        $this->parent = $router;
    }

    /**
     * Process a HTTP request.
     *
     * @param string  $name Optional route name.
     * @param Request $req  HTTP request.
     *
     * @return Response
     */
    public function run($name, Request $req)
    {
        if (!($route = $this->findMatchedRoute($name, $req))) {
            return $this->runError(404, $req);
        }
        try {
            $res = $route->response();
        } catch (\Exception $ex) {
            if (defined(get_class($ex).'::HTTP_STATUS_CODE')) {
                $statusCode = $ex::HTTP_STATUS_CODE;
            } else {
                $statusCode = 500;
            }
            return $route->runError($statusCode, $ex);
        }
        $res = $this->toResponse($res);
        if ('HEAD' === $req->getMethod()) {
            $res->setContent('');
        }

        return $res;
    }

    /**
     * Find a route by the HTTP request.
     *
     * @param string  $name   Optional route name.
     * @param Request $req    HTTP request.
     * @param string  $prefix URI prefix of the group.
     *
     * @return BoundRoute|null
     */
    public function findMatchedRoute($name, Request $req, $prefix = '')
    {
        if ($name) {
            return $this->findNamedRoute($name, $req, $prefix);
        }
        foreach ($this->routes as $route) {
            if ($route instanceof Route) {
                if ($route = $route->matchRequest($req, $prefix)) {
                    return $route;
                }
                continue;
            }
            list($path, $router) = $route;
            if ($route = $router->findMatchedRoute($name, $req, $prefix.$path)) {
                return $route;
            }
        }
    }

    /**
     * Process an HTTP error.
     *
     * @param int        $status HTTP status code.
     * @param Request    $req    HTTP request.
     * @param \Exception $ex     Exception.
     *
     * @return Response
     */
    public function runError($status, Request $req, \Exception $ex = null)
    {
        if (!($handler = $this->findErrorHandler($status))) {
            return new Response((string) $ex, $status);
        }
        $dp = new Dispatcher($this->c);
        $bag = new ParameterBag();
        $bag->setRequest($req);
        $bag->addArray(
            [
                'e'         => $ex,
                'ex'        => $ex,
                'err'       => $ex,
                'error'     => $ex,
                'exception' => $ex,
                'req'       => $req,
                'request'   => $req,
                'router'    => $this->facade,
            ]
        );
        $dp->setNamedArgs($bag);
        $dp->setTypedArg('Exception', $ex);
        $dp->setTypedArg('Ranyuen\Little\Request', $req);
        $dp->setTypedArg('Symfony\Component\HttpFoundation\Request', $req);
        $dp->setTypedArg('Ranyuen\Little\Router', $this->facade);
        try {
            $res = $dp->invoke($handler);
        } catch (\Exception $ex2) {
            if (500 === $status) {
                return new Response((string) $ex2, 500);
            }

            return $this->runError(500, (string) $ex2);
        }
        $res = $this->toResponse($res);
        $res->setStatusCode($status);

        return $res;
    }

    /**
     * Find an error handler.
     *
     * @param int $status HTTP status code.
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

    private function findNamedRoute($name, Request $req, $prefix = '')
    {
        if (isset($this->namedRoutes[$name])) {
            $dp = new Dispatcher($this->c);
            $bag = new ParameterBag();
            $bag->setRequest($req);
            $bag->addArray(
                [
                    'req'     => $req,
                    'request' => $req,
                    'router'  => $this->facade,
                ]
            );
            $dp->setNamedArgs($bag);
            $dp->setTypedArg('Ranyuen\Little\Request', $req);
            $dp->setTypedArg('Symfony\Component\HttpFoundation\Request', $req);
            $dp->setTypedArg('Ranyuen\Little\Router', $this->facade);

            return new BoundRoute($this->namedRoutes[$name], $this->facade, $req, $dp);
        }
        foreach ($this->routes as $route) {
            if (!(is_array($route) && $route[1] instanceof Router)) {
                continue;
            }
            list($path, $router) = $route;
            if ($route = $router->findMatchedRoute($name, $req, $prefix.$path)) {
                return $route;
            }
        }
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
