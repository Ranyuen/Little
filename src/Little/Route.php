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
 * Route facade.
 */
class Route
{
    /** @var RouteService */
    private $service;
    /** @var Router */
    private $router;

    /**
     * @param Container $c          DI container.
     * @param Router    $router     Owner of this route.
     * @param string    $path       Path DSL.
     * @param mixed     $controller Invokable.
     */
    public function __construct(Container $c, Router $router, $path, $controller)
    {
        $this->router = $router;
        $this->service = new RouteService($c, $router, $this, $controller);
        $this->service->rawPath = $path;
    }

    public function __call($name, $args)
    {
        $aliases = [
            'bind'       => 'name',
            'conditions' => 'assert',
            'method'     => 'via',
        ];
        if (isset($aliases[$name])) {
            return call_user_func_array([$this, $aliases[$name]], $args);
        }
        $routerMethods = [
            'map', 'error', 'group', 'run', 'handle',
            'match', 'mount',
            'get', 'post', 'put', 'delete', 'options', 'patch',
        ];
        if (in_array($name, $routerMethods)) {
            return call_user_func_array([$this->router, $name], $args);
        }

        return call_user_func_array([$this->service, $name], $args);
    }

    /**
     * via('GET', 'POST') or via(['GET', 'POST']).
     *
     * @return this
     */
    public function via()
    {
        if (is_array(func_get_arg(0))) {
            $methods = func_get_arg(0);
        } else {
            $methods = func_get_args();
        }
        foreach ($methods as $method) {
            $this->service->addMethod(strtoupper($method));
        }

        return $this;
    }

    /**
     * @param string $name Name.
     *
     * @return this
     */
    public function name($name)
    {
        $this->router->addRoute($this, $name);

        return $this;
    }

    /**
     * @param callable|string $cond Condition.
     *
     * @return this
     */
    public function assert($cond)
    {
        $this->service->addCondition($cond);

        return $this;
    }
}
