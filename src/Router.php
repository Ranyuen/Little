<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2021 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Little
 */

declare(strict_types=1);

namespace Ranyuen\Little;

use Ranyuen\Di\Container;

/**
 * Router facade.
 */
class Router
{
    /**
     * Routing plugins.
     *
     * @var array
     */
    private static $plugins = [];

    /**
     * Register a routing plugin.
     *
     * @param string $class Class name of the plugin.
     *
     * @return void
     */
    public static function plugin($class)
    {
        if (! class_exists($class)) {
            return;
        }
        foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            self::$plugins[$method->name] = $class;
        }
    }

    /**
     * Service.
     *
     * @var RouterService
     */
    private $service;
    /**
     * DI container.
     *
     * @var Container
     */
    private $c;

    /**
     * Constructor.
     *
     * @param Container $c DI container.
     */
    public function __construct(Container $c = null)
    {
        if (! $c) {
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
     * Map a controller by path.
     *
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
        $this->service->errorHandlers[$status] = $controller;

        return $this;
    }

    /**
     * Create a controller group.
     *
     * @param string        $path   Path DSL.
     * @param self|callback $router Child router, or child router configuration.
     *
     * @return this
     */
    public function group($path, $router)
    {
        if (! ($router instanceof self)) {
            $r = new self($this->c);
            $router($r);

            return $this->group($path, $r);
        }
        $this->service->addGroup($path, $router);

        return $this;
    }

    /**
     * Add a StackPHP middleware.
     *
     * @param string $middleware Class name.
     *
     * @return this
     */
    public function stack($middleware)
    {
        $this->service->stacks[] = $middleware;
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
}
