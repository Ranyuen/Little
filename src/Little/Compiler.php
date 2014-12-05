<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Little;

use Symfony\Component\Routing\Route as BaseRoute;
use Symfony\Component\Routing\RouteCompiler;

/**
 */
class Compiler
{
    /**
     * @param string $path
     *
     * @return string
     */
    public function compile($path)
    {
        $compiledRoute = (new RouteCompiler())->compile(new BaseRoute($path));

        return $compiledRoute->getRegex();
    }
}