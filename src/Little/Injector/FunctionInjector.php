<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Little\Injector;

use Ranyuen\Di\Container;

class FunctionInjector
{
    /** @var ContainerSet */
    private $container;
    /** @var callable */
    private $invocation;
    /** @var ReflectionParameter[] */
    private $params = [];

    private static function isRegex($str)
    {
        if (!is_string($str)) {
            return false;
        }
        if (!preg_match('/^[^A-Za-z0-9\\\s]/', $str)) {
            return false;
        }
        $delimiter = $str[0];
        $delimiters = [
            '(' => ')',
            '{' => '}',
            '[' => ']',
            '<' => '>',
        ];
        if (isset($delimiters[$delimiter])) {
            $delimiter = $delimiters[$delimiter];
        }

        return !!preg_match('/'.preg_quote($delimiter, '/').'[imsxeADSUXJu]*$/', $str);
    }

    public function __construct(ContainerSet $set, $func = null, $obj = null)
    {
        $this->container = $set;
        if (!is_null($func)) {
            $this->registerFunc($func, $obj);
        }
    }

    /**
     * @param string|callable|\ReflectionFunctionAbstract $func
     * @param object                                      $obj  This of the method.
     *
     * @return this
     *
     * @throws \InvalidArgumentException
     */
    public function registerFunc($func, $obj = null)
    {
        if (is_callable($func)) {
            $this->registerCallable($func);

            return $this;
        }
        if (is_string($func) && (false !== strpos($func, '@'))) {
            $this->registerMethod($func, $obj);

            return $this;
        }
        if (self::isRegex($func)) {
            $this->registerRegex($func);

            return $this;
        }
        if ($func instanceof \ReflectionFunctionAbstract) {
            $this->registerReflection($func, $obj);

            return $this;
        }
        throw new \InvalidArgumentException('Not a callable: '.(string) $func);
    }

    /**
     * @param array $args
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function invoke(array $args = [])
    {
        foreach ($this->params as $i => $param) {
            if (is_null($var = $this->container->getByParam($param))) {
                continue;
            }
            array_splice($args, $i, 0, [$var]);
        }

        return call_user_func_array($this->invocation, $args);
    }

    private function registerCallable(callable $func)
    {
        $this->invocation = $func;
        if ($func instanceof \Closure
            || (is_string($func) && function_exists($func))) {
            $this->params = (new \ReflectionFunction($func))->getParameters();
        } elseif (is_array($func)) {
            list($class, $method) = $func;
            if (is_object($class)) {
                $class = get_class($class);
            }
            $this->params = (new \ReflectionMethod($class, $method))->getParameters();
        } else {
            $this->params = (new \ReflectionMethod($func))->getParameters();
        }
    }

    private function registerMethod($func, $obj = null)
    {
        list($class, $method) = explode('@', $func);
        $this->invocation = function () use ($obj, $class, $method) {
            if (!is_object($obj)) {
                if ($obj = $this->container[$class]) {
                } elseif ($obj = $this->container->getByType($class)) {
                } else {
                    $obj = $this->container->newInstance($class);
                }
            }

            return call_user_func_array([$obj, $method], func_get_args());
        };
        $this->params = (new \ReflectionMethod($class, $method))->getParameters();
    }

    private function registerRegex($pattern)
    {
        $this->invocation = function ($subject, array &$matches = null, $flags = 0, $offset = 0) use ($pattern) {
            return preg_match($pattern, $subject, $matches, $flags, $offset);
        };
        $this->params = (new \ReflectionFunction($this->invocation))->getParameters();
    }

    private function registerReflection(\ReflectionFunctionAbstract $func, $obj = null)
    {
        if ($func instanceof \ReflectionFunction) {
            $this->invocation = function () use ($func) {
                return $func->invokeArgs(func_get_args());
            };
            $this->params = $func->getParameters();
        } elseif ($func instanceof \ReflectionMethod) {
            $this->invocation = function () use ($func, $obj) {
                if (!is_object($obj)) {
                    $obj = $this->container
                        ->newInstance($func->getDeclaringClass()->name);
                }

                return $func->invokeArgs($obj, func_get_args());
            };
            $this->params = $func->getParameters();
        }
        throw new Exception();
    }
}
