<?php
namespace Fixture;

function fiFunc($test, $momonga, Momonga $m)
{
    $test->assertInstanceOf('Fixture\Momonga', $momonga);
    $test->assertInstanceOf('Fixture\Momonga', $m);
}

function getFiClosure()
{
    return function ($test, $momonga, Momonga $m) {
        $test->assertInstanceOf('Fixture\Momonga', $momonga);
        $test->assertInstanceOf('Fixture\Momonga', $m);
    };
}

class FiClass
{
    public static function fiStatic($test, $momonga, Momonga $m)
    {
        $test->assertInstanceOf('Fixture\Momonga', $momonga);
        $test->assertInstanceOf('Fixture\Momonga', $m);
    }

    public function fiMethod($test, $momonga, Momonga $m)
    {
        $test->assertInstanceOf('Fixture\Momonga', $momonga);
        $test->assertInstanceOf('Fixture\Momonga', $m);
    }
}
