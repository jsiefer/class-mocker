<?php

namespace JSiefer\ClassMocker\TestClasses;

use JSiefer\ClassMocker\Mock\BaseMock;
use JSiefer\ClassMocker\next;


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
    protected function protectedMethod($a)
    {
        if ($a === 0) {
            return next::caller();
        }
        return $a + 10;
    }

    /**
     * Simple protected method
     *
     * @param $a
     * @return mixed
     */
    private function privateMethod($a)
    {
        return $a + 10;
    }

    /**
     * Simple protected method
     *
     * @param $a
     * @return mixed
     */
    public function publicMethod($a)
    {
        return $a + 10;
    }

}
