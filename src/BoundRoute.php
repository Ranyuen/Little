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
use Ranyuen\Little\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * HTTP Requested Route.
 *
 * DCI roled Route :)
 */
class BoundRoute implements HttpKernelInterface
{
    /**
     * Matched route.
     *
     * @var Route
     */
    private $route;
    /**
     * Router that holds the route.
     *
     * @var Router
     */
    private $router;
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
     * @param Route      $route  Matched route.
     * @param Router     $router Router that holds the route.
     * @param Request    $req    HTTP request.
     * @param Dispatcher $dp     DI container.
     */
    public function __construct(Route $route, Router $router, Request $req, Dispatcher $dp)
    {
        $this->route  = $route;
        $this->router = $router;
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
        $app = $this;
        foreach (array_reverse($this->router->getStacks()) as $stackClass) {
            $app = new $stackClass($app);
        }
        return $app->handle($this->req);
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

    // @codingStandardsIgnoreStart
    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $req   A Request instance
     * @param int     $type  The type of the request
     *                       (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @SuppressWarnings(PHPMD)
     */
    public function handle(Request $req, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        return $this->router->toResponse($this->route->response($this->dp));
    }
    // @codingStandardsIgnoreEnd
}
