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

use Ranyuen\Little\Injector\ContainerSet;

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
    /** @var ContainerSet */
    private $c;

    /**
     * @param Router       $router The router that has the route.
     * @param Route        $route  Matched route.
     * @param Request      $req    HTTP Request.
     * @param ContainerSet $c      DI container.
     */
    public function __construct(Router $router, Route $route, Request $req, ContainerSet $c)
    {
        $this->router = $router;
        $this->route  = $route;
        $this->req    = $req;
        $this->c      = $c;
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
        $this->c->addArray($vars);

        return $this->route->response($this->c);
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
