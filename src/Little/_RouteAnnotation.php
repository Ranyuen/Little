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

use Ranyuen\Di\Annotation;

/**
 */
class _RouteAnnotation extends Annotation
{
    /**
     * @param \ReflectionClass $class
     *
     * @return string|null
     */
    public function getGroup($class)
    {
        $group = $this->getValues($class, 'Route');
        if (!isset($group[0])) {
            return;
        }

        return $group[0];
    }

    /**
     * @param \ReflectionMethod $method Target method.
     *
     * @return array
     */
    public function getRoutes($method)
    {
        return array_map(
            function ($route) {
                list($method, $path) = explode(' ', $route);

                return [strtoupper($method), $path];
            },
            $this->getValues($method, 'Route')
        );
    }
}
