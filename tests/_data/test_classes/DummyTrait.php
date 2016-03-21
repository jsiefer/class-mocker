<?php

namespace JSiefer\ClassMocker\TestClasses;

/**
 * Class DummyTrait
 *
 * A simple trait fot testing purpose
 */
trait DummyTrait
{
    /**
     * @return string
     */
    public function talk()
    {
        return 'DummyTrait:talk';
    }
}
