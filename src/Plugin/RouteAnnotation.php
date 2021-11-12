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

use Ranyuen\Di\Reflection\AbstractAnnotation;

/**
 * Route annotation.
 */
class RouteAnnotation extends AbstractAnnotation
{
    /**
     * Get routing group path of the controller.
     *
     * @param \ReflectionClass $class Controller class.
     *
     * @return array|null
     */
    public function getGroup(\ReflectionClass $class)
    {
        $group = $this->getValues($class, 'Route');
        if (! isset($group[0])) {
            return;
        }

        return $group;
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
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $routes = $this->fetchRoutes($class, $method, $routes);
        }
        $group = $this->getGroup($class);
        if ($group) {
            $routes = [
                'group' => [
                    $group[0] => $routes,
                ],
            ];
            if (isset($group['stack'])) {
                if (! is_array($group['stack'])) {
                    $routes['group'][$group[0]]['stack'] = [$group['stack']];
                } else {
                    $routes['group'][$group[0]]['stack'] = $group['stack'];
                }
            }
        }

        return $routes;
    }

    private function fetchRoutes(\ReflectionClass $class, \ReflectionMethod $method, array $routes)
    {
        $vals = $this->getEachValue($method, 'Route');
        if (! ($vals)) {
            return $routes;
        }
        foreach ($vals as $val) {
            $routes = $this->mergeValue($routes, $val, $class, $method);
        }
        return $routes;
    }

    private function mergeValue($routes, $val, $class, $method)
    {
        if (isset($val[0])) {
            $path = $val[0];
            $routes['map'][] = [$path, "$class->name@$method->name"];
            foreach (['via', 'name', 'assert'] as $key) {
                if (isset($val[$key])) {
                    $routes['map'][count($routes['map']) - 1][$key] = $val[$key];
                }
            }
        }
        if (isset($val['error'])) {
            if (is_array($val['error'])) {
                foreach ($val['error'] as $status) {
                    $routes['error'][intval($status)]
                        = "$class->name@$method->name";
                }
            } else {
                $routes['error'][intval($val['error'])]
                    = "$class->name@$method->name";
            }
        }

        return $routes;
    }
}
