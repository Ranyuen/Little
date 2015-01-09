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
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Router facade.
 */
class Router implements HttpKernelInterface
{
    /** @var RouterService */
    private $service;
    /** @var Container */
    private $c;

    /**
     * @param Container $c
     */
    public function __construct(Container $c = null)
    {
        if (!$c) {
            $c = new Container();
        }
        $this->service = new RouterService($this, $c);
        $this->c       = $c;
    }

    public function __call($name, $args)
    {
        $aliases = [
            'match' => 'map',
            'mount' => 'group',
        ];
        if (isset($aliases[$name])) {
            return call_user_func_array([$this, $aliases[$name]], $args);
        }
        $httpMethods = ['get', 'post', 'put', 'delete', 'options', 'patch'];
        if (in_array($name, $httpMethods)) {
            return call_user_func_array([$this, 'map'], $args)->via($name);
        }

        return call_user_func_array([$this->service, $name], $args);
    }

    /**
     * @param string          $path
     * @param callable|string $controller
     *
     * @return Route
     */
    public function map($path, $controller)
    {
        $route = new Route($this->c, $this, $path, $controller);
        $this->service->addRoute($route);

        return $route;
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

            return $this->service->runError($status, $req, $ex);
        }
        $this->service->addErrorHandler($status, $controller);

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
            $r = new self($this->c);
            $router($r);

            return $this->group($path, $r);
        }
        $this->service->addGroup($path, $router);

        return $this;
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

        return $this->service->run($name, $req);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
    }
}
