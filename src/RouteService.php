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
 * Route service.
 */
class RouteService
{
    /**
     * Yet-compiled path.
     *
     * @var string
     */
    public $rawPath;
    /**
     * Conditions.
     *
     * @var RouteCondition[]
     */
    public $conditions = [];

    /**
     * DI container.
     *
     * @var Container
     */
    private $c;
    /**
     * Router that holds this route.
     *
     * @var Router
     */
    private $router;
    /**
     * Facade.
     *
     * @var Route
     */
    private $facade;
    /**
     * Controller.
     *
     * @var mixed
     */
    private $controller;
    /**
     * HTTP methods.
     *
     * @var string[]
     */
    private $methods = [];

    /**
     * Constructor.
     *
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
     * Set HTTP method.
     *
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
     * Match and process an HTTP request.
     *
     * @param Request $req    HTTP request.
     * @param string  $prefix URI prefix of the group.
     *
     * @return RequestedRoute|null
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function matchRequest(Request $req, $prefix = '')
    {
        if (!in_array($req->getMethod(), $this->methods)) {
            return;
        }
        if ('/' !== substr($this->rawPath, 0, 1) && Dispatcher::isRegex($this->rawPath)) {
            preg_match('#\A(.)(.*)(.[imsxeADSUXJu]*)\z#', $this->rawPath, $matches);
            $compiledPath = $matches[1].'\A(?:'.preg_quote($prefix, $matches[1]).')'.$matches[2].'\z'.$matches[3];
        } else {
            $compiledPath = (new PathCompiler($prefix.$this->rawPath))->compile();
        }
        if (!preg_match($compiledPath, $req->getPathInfo(), $matches)) {
            return;
        }
        $bag = new ParameterBag();
        $bag->setRequest($req);
        $bag->addArray(
            [
                'matches' => $matches,
                'req'     => $req,
                'request' => $req,
                'router'  => $this->router,
            ]
        );
        $bag->addArray($matches);
        $bag->addArray($this->c);
        $dp = new Dispatcher($this->c);
        $dp->setNamedArgs($bag);
        $dp->setTypedArg('Ranyuen\Little\Request', $req);
        $dp->setTypedArg('Symfony\Component\HttpFoundation\Request', $req);
        $dp->setTypedArg('Ranyuen\Little\Router', $this->router);
        foreach ($this->conditions as $cond) {
            if (!$cond->isMatch($bag, $dp)) {
                return;
            }
        }

        return new RequestedRoute($this->router, $this->facade, $req, $dp);
    }

    /**
     * Run the controller.
     *
     * @param Dispatcher $dp DI container.
     *
     * @return mixed
     */
    public function response(Dispatcher $dp)
    {
        return $dp->invoke($this->controller);
    }
}
