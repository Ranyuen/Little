<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Little\Plugin;

use Ranyuen\Di\Reflection\Annotation;

/**
 * Route annotation.
 */
class RouteAnnotation extends Annotation
{
    /**
     * Get routing group path of the controller.
     *
     * @param \ReflectionClass $class Controller class.
     *
     * @return string|null
     */
    public function getGroup(\ReflectionClass $class)
    {
        $group = $this->getValues($class, 'Route');
        if (!isset($group[0])) {
            return;
        }

        return $group[0];
    }

    /**
     * Get routing config of the controller.
     *
     * @param \ReflectionClass $class Controller class.
     *
     * @return array
     */
    public function getRoutes(\ReflectionClass $class)
    {
        $routes = [];
        foreach ($class->getMethods() as $method) {
            $routes = $this->fetchRoute($class, $method, $routes);
        }
        if ($group = $this->getGroup($class)) {
            $routes = ['group' => [$group => $routes]];
        }

        return $routes;
    }

    private function fetchRoute(\ReflectionClass $class, \ReflectionMethod $method, array $routes)
    {
        if (!($route = $this->getValues($method, 'Route'))) {
            return $routes;
        }
        if (isset($route[0])) {
            $path = $route[0];
            $routes['map'][$path] = ["$class->name@$method->name"];
            if (isset($route['via'])) {
                $routes['map'][$path]['via'] = $route['via'];
            }
            if (isset($route['name'])) {
                $routes['map'][$path]['name'] = $route['name'];
            }
            if (isset($route['assert'])) {
                $routes['map'][$path]['assert'] = $route['assert'];
            }
        }
        if (isset($route['error'])) {
            if (is_array($route['error'])) {
                foreach ($route['error'] as $status) {
                    $routes['error'][intval($status)]
                        = "$class->name@$method->name";
                }
            } else {
                $routes['error'][intval($route['error'])]
                    = "$class->name@$method->name";
            }
        }

        return $routes;
    }
}
