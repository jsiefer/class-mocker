<?php

namespace JSiefer\ClassMocker\TestClasses;

use JSiefer\ClassMocker\Mock\BaseMock;

/**
 * Class TestMock
 *
 * A test mock class that should be used as base
 * for all classes matching the @patten
 *
 * @pattern MyMock_*
 * @sort 80
 * @package JSiefer\ClassMocker
 */
class TestMock extends BaseMock
{

    /**
     * @return string
     */
    public function talk()
    {
        return 'TestMock:talk';
    }

    /**
     * @return string
     */
    public function jump()
    {
        return 'TestMock:jump';
    }

    /**
     * @return string
     */
    public function listen()
    {
        return 'TestMock:listen';
    }
}
