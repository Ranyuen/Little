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

namespace Ranyuen\Little;

use Ranyuen\Di\Dispatcher\Dispatcher;

/**
 * Route Condition.
 */
class RouteCondition
{
    /**
     * Factory.
     *
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
     * Factory.
     *
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

    /**
     * Invokable.
     *
     * @var mixed
     */
    private $invokable;
    /**
     * Target param name.
     *
     * @var string
     */
    private $name;
    /**
     * Condition.
     *
     * @var mixed
     */
    private $pattern;

    /**
     * Dose the param match the condition?
     *
     * @param ParameterBag $bag Params.
     * @param Dispatcher   $dp  Dispatcher with params.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function isMatch(ParameterBag $bag, Dispatcher $dp)
    {
        if ($this->invokable) {
            try {
                return $dp->invoke($this->invokable);
            } catch (\Exception $ex) {
                // This exception must be ignored.
                return false;
            }
        }
        $value = $bag[$this->name];
        if (is_null($value)) {
            return false;
        }
        if (! Dispatcher::isRegex($this->pattern)) {
            return $this->pattern === $value;
        }
        $value = (string) $value;

        return ! ! preg_match($this->pattern, $value, $m) && $m[0] === $value;
    }
}
