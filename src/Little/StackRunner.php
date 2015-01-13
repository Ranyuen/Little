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
 * Stack middlewares runner.
 */
class StackRunner implements HttpKernelInterface
{
    /** @var RouterService */
    private $service;
    /** @var string */
    private $name;

    /**
     * @param RouterService $service Service of the router.
     * @param string        $name    Optional request name.
     */
    public function __construct(RouterService $service, $name)
    {
        $this->service = $service;
        $this->name    = $name;
    }

    /**
     * @param Request  $req    HTTP request.
     * @param string[] $stacks Stack class names.
     *
     * @return Response
     */
    public function run(Request $req, array $stacks)
    {
        $app = $this;
        foreach (array_reverse($stacks) as $class) {
            $app = new $class($app);
        }

        return $app->handle($req, HttpKernelInterface::MASTER_REQUEST, true);
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int     $type    The type of the request
     *                         (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch   Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        return $this->service->run($this->name, $request);
    }
}
