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

/**
 * Wrap with ArrayAccess.
 */
class ParameterBag implements \ArrayAccess
{
    /**
     * Params.
     *
     * @var array
     */
    private $array = [];
    /**
     * Not arrays.
     *
     * @var ArrayAccess[]
     */
    private $arrays = [];
    /**
     * HTTP request.
     *
     * @var Request
     */
    private $req;

    /**
     * Add params through an array.
     *
     * @param array|ArrayAccess $array Array.
     *
     * @return void
     */
    public function addArray($array)
    {
        if (is_array($array)) {
            $this->array = array_merge($this->array, $array);
        } else {
            if (!($array instanceof \ArrayAccess)) {
                throw new Exception('Not an array: '.(string) $array);
            }
            $this->arrays[] = $array;
        }
    }

    /**
     * Add params in an HTTP request.
     *
     * @param Request $req HTTP request.
     *
     * @return void
     */
    public function setRequest(Request $req)
    {
        $this->req = $req;
    }

    public function offsetExists($offset)
    {
        if (isset($this->array[$offset])) {
            return true;
        }
        foreach ($this->arrays as $array) {
            if (isset($array[$offset])) {
                return true;
            }
        }
        return !is_null($this->req->get($offset));
    }

    public function offsetGet($offset)
    {
        if (isset($this->array[$offset])) {
            return $this->array[$offset];
        }
        foreach ($this->arrays as $array) {
            if (isset($array[$offset])) {
                return $array[$offset];
            }
        }
        if (!is_null($var = $this->req->get($offset))) {
            return $var;
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
        foreach ($this->arrays as $array) {
            unset($array[$offset]);
        }
        $this->req->set($offset, null);
    }
}
