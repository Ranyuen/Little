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

/**
 * Router facade.
 */
class Router
{
    /** @var array */
    private static $plugins = [];

    /**
     * @param string $class Class name of the plugin.
     *
     * @return void
     */
    public static function plugin($class)
    {
        if (!class_exists($class)) {
            return;
        }
        foreach ($class::METHODS as $method) {
            self::$plugins[$method] = $class;
        }
    }

    /** @var RouterService */
    private $service;
    /** @var Container */
    private $c;
    /** @var string[] */
    private $stacks = [];

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
        if (isset(self::$plugins[$name])) {
            $plugin = self::$plugins[$name];

            return call_user_func_array([new $plugin($this), $name], $args);
        }

        return call_user_func_array([$this->service, $name], $args);
    }

    /**
     * @param string $class This class must implements HttpKernelInterface.
     *
     * @return this
     */
    public function pushStack($class)
    {
        if (!in_array('Symfony\Component\HttpKernel\HttpKernelInterface', class_implements($class))) {
            return $this;
        }
        $this->stacks[] = $class;

        return $this;
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

        return (new StackRunner($this->service, $name))->run($req, $this->stacks);
    }
}
