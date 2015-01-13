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

use Symfony\Component\Routing\Route as BaseRoute;
use Symfony\Component\Routing\RouteCompiler;

/**
 * Path DSL compiler.
 */
class Compiler
{
    /**
     * Compile the route path DSL to a regex.
     *
     * @param string $path Path DSL.
     *
     * @return string
     */
    public function compile($path)
    {
        return (new RouteCompiler())->compile(new BaseRoute($path))->getRegex();
    }
}
