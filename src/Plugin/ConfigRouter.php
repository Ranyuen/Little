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
namespace Ranyuen\Little\Plugin;

use Ranyuen\Little\Router;
use Ranyuen\Little\RoutingPlugin;

/**
 * Routing plugin to route by array config.
 *
 * @example
 *     Router::plugin('Ranyuen\Little\Plugin\ConfigRouter');
 *     $r = new Router();
 *     $r->routeByConfig([
 *         'map' => [
 *             ['/', 'IndexController@index'],
 *         ],
 *         'error' => [
 *             500 => 'IndexController@error500',
 *         ],
 *         'group' => [
 *             '/blog' => [
 *                 'map' => [
 *                     [
 *                         '/:page',
 *                         'BlogController@index',
 *                         'name'   => 'blog_index',
 *                         'assert' => ['page' => '/\A\d+\z/'],
 *                     ],
 *                     ['/show/:id', 'BlogController@show'],
 *                 ],
 *                 'error' => [
 *                     404 => 'BlogController@notFound',
 *                 ],
 *             ],
 *         ],
 *     ]);
 */
class ConfigRouter implements RoutingPlugin
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
     * Route by config.
     *
     * @param array $config Routing config.
     *
     * @return Router
     */
    public function routeByConfig(array $config)
    {
        foreach ($config as $method => $val) {
            switch ($method) {
                case 'map':
                    $this->map($val);
                    break;
                case 'error':
                    $this->error($val);
                    break;
                case 'group':
                    $this->group($val);
                    break;
            }
        }

        return $this->router;
    }

    private function map(array $config)
    {
        foreach ($config as $val) {
            list($path, $controller) = $val;
            $route = $this->router->map($path, $controller);
            if (isset($val['via'])) {
                $route->via($val['via']);
            } else {
                $route->via('GET');
            }
            if (isset($val['name'])) {
                $route->name($val['name']);
            }
            if (isset($val['assert'])) {
                if (!is_array($val['assert'])) {
                    $route->assert($val['assert']);
                } else {
                    foreach ($val['assert'] as $k => $cond) {
                        if (is_int($k)) {
                            $route->assert($cond);
                            continue;
                        }
                        $route->assert($k, $cond);
                    }
                }
            }
        }
    }

    private function error(array $config)
    {
        foreach ($config as $status => $controller) {
            if (!is_int($status)) {
                continue;
            }
            $this->router->error($status, $controller);
        }
    }

    private function group(array $config)
    {
        foreach ($config as $path => $val) {
            $this->router->group(
                $path,
                function (Router $r) use ($val) {
                    (new ConfigRouter($r))->routeByConfig($val);
                }
            );
        }
    }
}
