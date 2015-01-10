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
     * @param Container $c DI container.
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
     * @param string $path       Path DSL.
     * @param mixed  $controller Invokable.
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
     * @param int                     $status     HTTP status code.
     * @param callable|string|Request $controller Error controller.
     * @param Exception               $ex         Exception.
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
     * @param string        $path   Path DSL.
     * @param self|callback $router Child router, or child router configuration.
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
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int     $type    The type of the request
     *                         (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch   Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
    }
}
