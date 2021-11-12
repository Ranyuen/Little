<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2021 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Little
 */

declare(strict_types=1);

namespace Ranyuen\Little\Plugin;

use Ranyuen\Little\Router;
use Ranyuen\Little\RoutingPluginInterface;

/**
 * Routing plugin to route by controller's annotation.
 *
 * @example
 *     /** @Route('/blog') * /
 *     class BlogController {
 *         /**
 *          * @Route('/:page?',name=blog_index,assert={page='/\A\d+\z/'})
 *          * /
 *         public function index($page = 1) {
 *         }
 *
 *         /** @Route('/show/:id') * /
 *         public function show($id) {
 *         }
 *
 *         /** @Route(error=404) * /
 *         public function notFound() {
 *         }
 *     }
 *     Router::plugin('Ranyuen\Little\Plugin\ControllerAnnotationRouter');
 *     $r = new Router();
 *     $r->registerController('BlogController');
 */
class ControllerAnnotationRouter implements RoutingPluginInterface
{
    /**
     * Router.
     *
     * @var Router
     */
    private $router;

    public function __construct(Router $r)
    {
        $this->router = $r;
    }

    /**
     * Route by controller annotations.
     *
     * @param string $class Controller class name.
     *
     * @return Router
     */
    public function registerController($class)
    {
        $class = new \ReflectionClass($class);
        $config = (new RouteAnnotation())->getRoutes($class);

        return (new ConfigRouter($this->router))->routeByConfig($config);
    }
}
