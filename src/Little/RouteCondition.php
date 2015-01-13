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
use Ranyuen\Little\Injector\FunctionInjector;

/**
 * Route Condition.
 */
class RouteCondition
{
    /**
     * @param mixed $invokable Invokable.
     *
     * @return self
     */
    public static function createFromInvokable($invokable)
    {
        $cond = new RouteCondition();
        $cond->invokable = $invokable;

        return $cond;
    }

    /**
     * @param string $name    Variable name.
     * @param mixed  $pattern Expected pattern or value.
     *
     * @return self
     */
    public static function createFromPattern($name, $pattern)
    {
        $cond = new RouteCondition();
        list($cond->name, $cond->pattern) = [$name, $pattern];

        return $cond;
    }

    /** @var mixed */
    private $invokable;
    /** @var string */
    private $name;
    /** @var mixed */
    private $pattern;

    /**
     * @param ContainerSet $c Params.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function isMatch(ContainerSet $c)
    {
        if ($this->invokable) {
            $injector = new FunctionInjector($c);
            $injector->registerFunc($this->invokable);
            try {
                return $injector->invoke();
            } catch (\Exception $ex) { // This exception must be ignored.
                return false;
            }
        }
        $value = $c[$this->name];
        if (is_null($value)) {
            return false;
        }
        if (!FunctionInjector::isRegex($this->pattern)) {
            return $this->pattern === $value;
        }

        return !!preg_match($this->pattern, (string) $value);
    }
}
