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

    /**
     * @param Router  $router
     * @param Route   $route
     * @param Request $req
     */
    public function __construct(Router $router, Route $route, Request $req)
    {
        $this->router = $router;
        $this->route  = $route;
        $this->req    = $req;
    }

    /**
     * @param array $var
     *
     * @return mixed
     */
    public function response(array $var = [])
    {
        return $this->route->response($this->req, $var);
    }

    /**
     * @param int $status
     * @param \Exception $ex
     *
     * @return Response
     */
    public function runError($status, \Exception $ex = null)
    {
        return $this->router->runError($status, $this->req, $ex);
    }
}
