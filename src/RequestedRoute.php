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

use Ranyuen\Di\Dispatcher\Dispatcher;

/**
 * HTTP Requested Route.
 *
 * DCI roled Route :)
 */
class RequestedRoute
{
    /**
     * Router that holds the route.
     *
     * @var Router
     */
    private $router;
    /**
     * Matched route.
     *
     * @var Route
     */
    private $route;
    /**
     * HTTP request.
     *
     * @var Request
     */
    private $req;
    /**
     * DI container.
     *
     * @var Dispatcher
     */
    private $dp;

    /**
     * Constructor.
     *
     * @param Router     $router Router that holds the route.
     * @param Route      $route  Matched route.
     * @param Request    $req    HTTP request.
     * @param Dispatcher $dp     DI container.
     */
    public function __construct(Router $router, Route $route, Request $req, Dispatcher $dp)
    {
        $this->router = $router;
        $this->route  = $route;
        $this->req    = $req;
        $this->dp     = $dp;
    }

    /**
     * Get a response.
     *
     * @param array $vars Extra values.
     *
     * @return mixed
     */
    public function response(array $vars = [])
    {
        $this->dp->setNamedArgs($vars);

        return $this->route->response($this->dp);
    }

    /**
     * Get an error response.
     *
     * @param int        $status HTTP status code.
     * @param \Exception $ex     Exception.
     *
     * @return Response
     */
    public function runError($status, \Exception $ex = null)
    {
        return $this->router->runError($status, $this->req, $ex);
    }
}
