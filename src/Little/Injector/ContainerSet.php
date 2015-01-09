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
use Ranyuen\Little\Request;

class ContainerSet implements \ArrayAccess
{
    /** @var Container[] */
    private $containers = [];
    /** @var Request[] */
    private $reqs = [];
    /** @var array */
    private $array = [];

    public function addContainer(Container $c)
    {
        $this->containers[] = $c;
    }

    public function addRequest(Request $req)
    {
        $this->reqs[] = $req;
    }

    public function addArray(array $a)
    {
        $this->array = array_merge($this->array, $a);
    }

    /**
     * @param \ReflectionParameter $param
     *
     * @return mixed|null
     */
    public function getByParam(\ReflectionParameter $param)
    {
        $type = null;
        if ($param->getClass()) {
            $type = $param->getClass()->name;
        }
        if (isset($this->array[$type])) {
            return $this->array[$type];
        }
        if ($var = $this->getByType($type)) {
            return $var;
        }

        return $this[$param->name];
    }

    public function newInstance($class)
    {
         $obj = $this->containers[0]->newInstance($class);
         foreach (array_slice($this->containers, 1) as $c) {
             $c->inject($obj);
         }
         $array = new Container();
         foreach ($this->array as $k => $v) {
             if (false !== strpos($k, '\\')) {
                 $array->bind($k, $k, $v);
             } else {
                 $array[$k] = $v;
             }
         }
         $array->inject($obj);
         return $obj;
    }

    public function getByType($class)
    {
        foreach ($this->containers as $c) {
            if ($var = $c->getByType($class)) {
                return $var;
            }
        }
    }

    public function offsetExists($offset)
    {
        if (isset($this->array[$offset])) {
            return true;
        }
        foreach ($this->containers as $c) {
            if (isset($c[$offset])) {
                return true;
            }
        }
        foreach ($this->reqs as $req) {
            if ($req->get($offset)
                || isset($req->$offset)
                || is_callable([$req, 'get'.ucfirst($offset)])) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet($offset)
    {
        if (isset($this->array[$offset])) {
            return $this->array[$offset];
        }
        foreach ($this->containers as $c) {
            if (isset($c[$offset])) {
                return $c[$offset];
            }
        }
        foreach ($this->reqs as $req) {
            if ($value = $req->get($offset)) {
                return $value;
            }
            if (isset($req->$offset)) {
                return $req->$offset;
            }
            if (is_callable([$req, 'get'.ucfirst($offset)])) {
                return call_user_func([$req, 'get'.ucfirst($offset)]);
            }
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
        foreach ($this->containers as $c) {
            unset($c[$offset]);
        }
        foreach ($this->reqs as $req) {
            if ($req->get($offset)) {
                $req->set($offset, null);
            }
            if (isset($req->$offset)) {
                unset($req->$offset);
            }
            if (is_callable([$req, 'set'.ucfirst($offset)])) {
                call_user_func_array([$req, 'set'.ucfirst($offset)], [null]);
            }
        }
    }
}
