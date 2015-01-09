<?php
require_once 'tests/Fixture/Momonga.php';
require_once 'tests/Fixture/functionInjectorCallables.php';

use Ranyuen\Di\Container;
use Ranyuen\Little\Injector\ContainerSet;
use Ranyuen\Little\Injector\FunctionInjector;

class FunctionInjectorTest extends PHPUnit_Framework_TestCase
{
    private function getInjector()
    {
        $c = new Container();
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Fixture\Momonga();
        });
        $set = new ContainerSet();
        $set->addContainer($c);

        return new FunctionInjector($set);
    }

    public function testIsRegex()
    {
        $isRegex = new ReflectionMethod('Ranyuen\Little\Injector\FunctionInjector', 'isRegex');
        $isRegex->setAccessible(true);
        $isRegex = $isRegex->getClosure();
        $regex = '/rEGeX/i';
        $this->assertTrue($isRegex($regex));
        $regex = '#rEGeX#i';
        $this->assertTrue($isRegex($regex));
        $regex = '(rEGeX)i';
        $this->assertTrue($isRegex($regex));
        $regex = '{rEGeX}i';
        $this->assertTrue($isRegex($regex));
        $regex = '[rEGeX]i';
        $this->assertTrue($isRegex($regex));
        $regex = '<rEGeX>i';
        $this->assertTrue($isRegex($regex));
    }

    public function testInvokeFunction()
    {
        $injector = $this->getInjector();
        $injector->registerFunc('Fixture\fiFunc');
        $injector->invoke([$this]);
    }

    public function testInvokeClosure()
    {
        $injector = $this->getInjector();
        $injector->registerFunc(Fixture\getFiClosure());
        $injector->invoke([$this]);
    }

    public function testInvokeStaticArr()
    {
        $injector = $this->getInjector();
        $injector->registerFunc(['Fixture\FiClass', 'fiStatic']);
        $injector->invoke([$this]);
    }

    public function testInvokeStaticStr()
    {
        $injector = $this->getInjector();
        $injector->registerFunc('Fixture\FiClass::fiStatic');
        $injector->invoke([$this]);
    }

    public function testInvokeMethodArr()
    {
        $injector = $this->getInjector();
        $injector->registerFunc([new Fixture\FiClass(), 'fiMethod']);
        $injector->invoke([$this]);
    }

    public function testInvokeMethodStr()
    {
        $injector = $this->getInjector();
        $injector->registerFunc('Fixture\FiClass@fiMethod', new Fixture\FiClass());
        $injector->invoke([$this]);
    }
}
