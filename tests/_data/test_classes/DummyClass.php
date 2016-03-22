<?php

namespace JSiefer\ClassMocker\TestClasses;

use JSiefer\ClassMocker\Mock\BaseMock;


/**
 * Class DummyClass
 *
 * A simple class for testing purpose
 */
class DummyClass extends BaseMock
{
    /**
     * Simple protected method
     *
     * @param $a
     * @return mixed
     */
    protected function secret($a)
    {
        return $a + 10;
    }
}
