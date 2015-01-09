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
     * @param Router  $router
     * @param Route   $route
     * @param Request $req
     * @param array   $pathVars
     */
    public function __construct(Router $router, Route $route, Request $req, array $pathVars)
    {
        $this->router   = $router;
        $this->route    = $route;
        $this->req      = $req;
        $this->pathVars = $pathVars;
    }

    /**
     * @param array $vars
     *
     * @return mixed
     */
    public function response(array $vars = [])
    {
        $vars = array_merge($this->pathVars, $vars);

        return $this->route->response($this->req, $vars);
    }

    /**
     * @param int        $status
     * @param \Exception $ex
     *
     * @return Response
     */
    public function runError($status, \Exception $ex = null)
    {
        return $this->router->runError($status, $this->req, $ex);
    }
}
