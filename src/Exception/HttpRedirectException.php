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
namespace Ranyuen\Little\Exception;

/**
 * HTTP 3xx redirect.
 */
abstract class HttpRedirectException extends \Exception
{
    public $location;

    /**
     * Conctructor.
     *
     * @param string $location Location header URI.
     */
    public function __construct($location)
    {
        $this->location = $location;
    }
}
