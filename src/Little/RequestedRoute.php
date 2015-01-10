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

/**
 * HTTP Requested Route.
 *
 * DCI roled Route :)
 */
class RequestedRoute
{
    /** @var Router */
    private $router;
    /** @var Route */
    private $route;
    /** @var Request */
    private $req;
    /** @var array */
    private $pathVars;

    /**
     * @param Router  $router   The router that has the route.
     * @param Route   $route    Matched route.
     * @param Request $req      HTTP Request.
     * @param array   $pathVars Values from the URI path.
     */
    public function __construct(Router $router, Route $route, Request $req, array $pathVars)
    {
        $this->router   = $router;
        $this->route    = $route;
        $this->req      = $req;
        $this->pathVars = $pathVars;
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
        $vars = array_merge($this->pathVars, $vars);

        return $this->route->response($this->req, $vars);
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
