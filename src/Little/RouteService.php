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
 * Route service.
 */
class RouteService
{
    /** @var string */
    public $rawPath;

    /** @var Container */
    private $c;
    /** @var Router */
    private $router;
    /** @var Route */
    private $facade;
    /** @var mixed */
    private $controller;
    /** @var string[] */
    private $methods = [];
    /** @var array */
    private $conditions = [];

    /**
     * @param Container $c          DI container.
     * @param Router    $router     Owner of this route.
     * @param Route     $facade     Route facade.
     * @param mixed     $controller Invokable.
     */
    public function __construct(Container $c, Router $router, Route $facade, $controller)
    {
        $this->c          = $c;
        $this->router     = $router;
        $this->facade     = $facade;
        $this->controller = $controller;
    }

    /**
     * @param string $method HTTP method.
     *
     * @return void
     */
    public function addMethod($method)
    {
        if (in_array($method, $this->methods)) {
            return;
        }
        $this->methods[] = $method;
        if ('GET' === $method) {
            $this->addMethod('HEAD');
        }
    }

    /**
     * @param RouteCondition $cond Condition.
     *
     * @return void
     */
    public function addCondition(RouteCondition $cond)
    {
        $this->conditions[] = $cond;
    }

    /**
     * @param Request $req    HTTP request.
     * @param string  $prefix URI prefix of the group.
     *
     * @return Route|null
     */
    public function matchRequest(Request $req, $prefix = '')
    {
        if (!in_array($req->getMethod(), $this->methods)) {
            return;
        }
        $compiledPath = (new Compiler())->compile($prefix.$this->rawPath);
        if (!preg_match($compiledPath, $req->getPathInfo(), $matches)) {
            return;
        }
        $set = new ContainerSet();
        $set->addContainer($this->c);
        $set->addRequest($req);
        $set->addArray(
            [
                'req'                                      => $req,
                'request'                                  => $req,
                'Ranyuen\Little\Request'                   => $req,
                'Symfony\Component\HttpFoundation\Request' => $req,
                'router'                                   => $this->router,
                'Ranyuen\Little\Router'                    => $this->router,
            ]
        );
        $set->addArray($matches);
        foreach ($this->conditions as $cond) {
            if (!$cond->isMatch($set)) {
                return;
            }
        }

        return new RequestedRoute($this->router, $this->facade, $req, $set);
    }

    /**
     * @param ContainerSet $c DI container.
     *
     * @return mixed
     */
    public function response(ContainerSet $c)
    {
        $injector = new FunctionInjector($c);
        $injector->registerFunc($this->controller);

        return $injector->invoke();
    }
}
