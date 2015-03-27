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
 * HTTP 413 error.
 */
class RequestEntityTooLarge extends \Exception
{
    const HTTP_STATUS_CODE = 413;
}
