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

/**
 */
class Route
{
    /** @var string */
    public $name;
    /** @var string */
    public $path;

    /** @var Router */
    private $router;
    /** @var string */
    private $pathRegex;
    /** @var array */
    private $methods = [];
    /** @var \ReflectionFunctionAbstract */
    private $controller;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function setPath($path)
    {
        $this->path = $path;
        $compiledPath = (new Compiler())->compile($path);
        $this->pathRegex = $compiledPath;
    }

    public function setController($controller)
    {
        if (!is_callable($controller)) {
            list($class, $method) = explode('@', $controller);
            $this->controller = new \ReflectionMethod($class, $method);
        } else {
            $this->controller = new \ReflectionFunction($controller);
        }
    }

    public function addMethods($methods)
    {
        if (in_array('GET', $methods)) {
            $methods[] = 'HEAD';
        }
        $this->methods = array_unique(array_merge($this->methods, $methods), SORT_REGULAR);
    }

    /**
     * @param Request $req
     * @param array   $vars
     *
     * @return Response|Exception|false
     */
    public function matchOrRun(Request $req, $vars = [])
    {
        $method = $req->getMethod();
        if ('HEAD' === $method) {
            $method = 'GET';
        }
        if (!in_array($method, $this->methods)) {
            return false;
        }
        if (!preg_match($this->pathRegex, $req->getRequestUri(), $matches)) {
            return false;
        }
        $vars = array_merge($vars, $matches);

        return $this->run($req, $vars);
    }

    /**
     * @param Request $req
     * @param array   $vars
     *
     * @return Response|Exception
     */
    public function run(Request $req, $vars = [])
    {
        $vars = array_merge(
            [
                'router'                                   => $this->router,
                'Ranyuen\Little\Router'                    => $this->router,
                'req'                                      => $req,
                'request'                                  => $req,
                'Symfony\Component\HttpFoundation\Request' => $req,
            ],
            $vars
        );
        $args = [];
        foreach ($this->controller->getParameters() as $param) {
            $args[] = $this->getVar($param, $req, $vars);
        }
        if ($this->controller instanceof \ReflectionMethod) {
            $obj = $this->router
                ->getContainer()
                ->newInstance($this->controller->getDeclaringClass()->getName());
            $this->createTmpContainer($vars)->inject($obj);
            try {
                $res = $this->controller->invokeArgs($obj, $args);
            } catch (\Exception $ex) {
                return $ex;
            }
        } else {
            try {
                $res = $this->controller->invokeArgs($args);
            } catch (\Exception $ex) {
                return $ex;
            }
        }
        $res = $this->toResponse($res);
        if ('HEAD' === $req->getMethod()) {
            $res->setContent('');
        }

        return $res;
    }

    private function getVar(\ReflectionParameter $param, Request $req, array $vars)
    {
        $container = $this->router->getContainer();
        $name = $param->getName();
        if ($type = $param->getClass()) {
            $type = $type->getName();
        }
        if (isset($vars[$type])) {
            return $vars[$type];
        }
        if ($var = $container->getByType($type)) {
            return $var;
        }
        if (isset($vars[$name])) {
            return $vars[$name];
        }
        if ($var = $req->get($name)) {
            return $var;
        }
        if (isset($container[$name])) {
            return $container[$name];
        }

        return;
    }

    private function createTmpContainer($vars)
    {
        $c = new Container();
        foreach ($vars as $name => $var) {
            if (false === strpos($name, '\\')) {
                $c[$name] = $var;
            } else {
                $c->bind($name, uniqid('route_', true), $var);
            }
        }

        return $c;
    }

    private function toResponse($value)
    {
        if ($value instanceof Response) {
            return $value;
        }
        if (is_int($value)) {
            return new Response('', $value);
        }

        return new Response((string) $value, 200);
    }
}
