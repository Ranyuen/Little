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
namespace Ranyuen\Little;

require_once 'vendor/hafriedlander/php-peg/autoloader.php';

use hafriedlander\Peg\Parser;
use Ranyuen\Di\Dispatcher\Dispatcher;

/**
 * Path DSL compiler.
 */
class PathCompiler extends Parser\Basic
{
/*!* Path

Main: Element *
    function Element(&$result, $sub) {
        if (!isset($result['val'])) {
            $result['val'] = [];
        }
        $result['val'][] = $sub;
    }

Element: Param | Splat | Text
    function Param(&$result, $sub) {
        $result['type']        = 'Param';
        $result['val']         = $sub['val'];
        $result['is_optional'] = $sub['is_optional'];
        $result['is_wildcard'] = $sub['is_wildcard'];
    }
    function Splat(&$result, $sub) {
        $result['type'] = 'Splat';
    }
    function Text(&$result, $sub) {
        $result['type']        = 'Text';
        $result['val']         = $sub['text'];
        $result['is_optional'] = $sub['is_optional'];
    }

Param: ':' > ParamName > Splat ? > Option ?
    function ParamName(&$result, $sub) {
        $result['val']         = $sub['text'];
        $result['is_wildcard'] = false;
        $result['is_optional'] = false;
    }
    function Splat(&$result, $sub) {
        $result['is_wildcard'] = true;
    }
    function Option(&$result, $sub) {
        $result['is_optional'] = true;
    }

ParamName: /\w+/

Splat: '*'

Text: TextContent > Option ?
    function TextContent(&$result, $sub) {
        $result['val']       = $sub['text'];
        $result['is_optional'] = false;
    }
    function Option(&$result, $sub) {
        $result['is_optional'] = true;
    }

TextContent: /[^:*?\w]/ | /\w+/

Option: '?'

*/

    private $conditions = [];

    public function __construct($path, $conditions = [])
    {
        parent::__construct($path);
        $this->conditions = $conditions;
    }

    /**
     * Compile the route path DSL to a regex.
     *
     * @param string $path Path DSL.
     *
     * @return string
     */
    public function compile()
    {
        $regex = '';
        foreach ($this->match_Main()['val'] as $element) {
            switch ($element['type']) {
                case 'Param':
                    $regex .= $this->compileParam($element);
                    break;
                case 'Splat':
                    $regex .= '(.*?)';
                    break;
                case 'Text':
                    $regex .= preg_quote($element['val'], '#');
                    if ($element['is_optional']) {
                        $regex .= '?';
                    }
                    break;
            }
        }
        return "#\A$regex\z#";
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function compileParam($element)
    {
        $regex = '';
        if (isset($this->conditions[$element['val']]) && is_string($cond = $this->conditions[$element['val']])) {
            if (Dispatcher::isRegex($cond)) {
                preg_match($cond, '#\A.(.*).[imsxeADSUXJu]*\z#', $matches);
                $cond = $matches[1];
            } else {
                $cond = preg_quote($cond, '#');
            }
            $regex .= '(?<'.$element['val'].'>'.$cond.')';
        } elseif ($element['is_wildcard']) {
            $regex .= '(?<'.$element['val'].'>.+?)';
        } else {
            $regex .= '(?<'.$element['val'].'>\w+?)';
        }
        if ($element['is_optional']) {
            $regex .= '?';
        }
        return $regex;
    }
}
// vim:ft=php:
