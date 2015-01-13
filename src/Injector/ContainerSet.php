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

/**
 * Set of DI Containers.
 */
class ContainerSet implements \ArrayAccess
{
    /** @var Container[] */
    private $containers = [];
    /** @var Request[] */
    private $reqs = [];
    /** @var array */
    private $array = [];
    /** @var Container */
    private $arrayContainer;

    /**
     * Add a container to the set.
     *
     * @param Container $c Container.
     *
     * @return void
     */
    public function addContainer(Container $c)
    {
        $this->containers[] = $c;
    }

    /**
     * Add a HTTP Request to the set.
     *
     * @param Request $req Container.
     *
     * @return void
     */
    public function addRequest(Request $req)
    {
        $this->reqs[] = $req;
    }

    /**
     * Add an array to the set.
     *
     * @param array $a Container.
     *
     * @return void
     */
    public function addArray(array $a)
    {
        $this->array = array_merge($this->array, $a);
        $this->arrayContainer = null;
    }

    /**
     * Get a value from the containers by the ReflectionParameter.
     *
     * @param \ReflectionParameter $param Key.
     *
     * @return mixed|null
     */
    public function getByParam(\ReflectionParameter $param)
    {
        if ($type = $param->getClass()) {
            $type = $type->name;
            if ($val = $this->getByType($type)) {
                return $val;
            }
            if ($val = $this[$type]) {
                return $val;
            }
        }

        return $this[$param->name];
    }

    /**
     * Create a new instance of the class.
     *
     * @param string $class Class name.
     *
     * @return mixed
     */
    public function newInstance($class)
    {
        $obj = $this->containers[0]->newInstance($class);
        foreach (array_slice($this->containers, 1) as $c) {
            $c->inject($obj);
        }
        if (is_null($this->arrayContainer)) {
            $this->arrayContainer = new Container();
            foreach ($this->array as $k => $v) {
                if (false !== strpos($k, '\\')) {
                    $this->arrayContainer->bind($k, $k, $v);
                } else {
                    $this->arrayContainer[$k] = $v;
                }
            }
        }
        $this->arrayContainer->inject($obj);

        return $obj;
    }

    /**
     * Get a value from the containers by the class name.
     *
     * @param string $class Class name.
     *
     * @return mixed
     */
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
                || is_callable([$req, 'get'.ucfirst($offset)])
            ) {
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
            if ($val = $req->get($offset)) {
                return $val;
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
        $this->arrayContainer = null;
    }

    public function offsetUnset($offset)
    {
        if (isset($this->array[$offset])) {
            unset($this->array[$offset]);
            $this->arrayContainer = null;
        }
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
