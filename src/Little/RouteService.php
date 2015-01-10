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
     * @param string $varName Variable name.
     * @param mixed  $cond    Invokable.
     *
     * @return void
     */
    public function addCondition($varName, $cond)
    {
        $this->conditions[$varName] = $cond;
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
        $set->addArray($matches);
        $injector = new FunctionInjector($set);
        foreach ($this->conditions as $varName => $cond) {
            if (!isset($set[$varName])) {
                return;
            }
            $injector->registerFunc($cond);
            try {
                if (!$injector->invoke($set[$varName])) {
                    return;
                }
            } catch (\Exception $ex) { // This exception must be ignored.
                return;
            }
        }

        return new RequestedRoute($this->router, $this->facade, $req, $matches);
    }

    /**
     * @param Request $req  HTTP request.
     * @param array   $vars Extra values.
     *
     * @return mixed
     */
    public function response(Request $req, array $vars = [])
    {
        $set = new ContainerSet();
        $set->addContainer($this->c);
        $set->addRequest($req);
        $set->addArray($vars);
        $injector = new FunctionInjector($set);
        $injector->registerFunc($this->controller);

        return $injector->invoke();
    }
}
