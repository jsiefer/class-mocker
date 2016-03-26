<?php

namespace JSiefer\ClassMocker\TestClasses;


/**
 * Class TraitSample
 *
 * A test trait dummy for testing trait usage
 *
 * @package JSiefer\ClassMocker
 */
trait InvalidTrait
{
    /**
     * This should not be allowed
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        return 'TraitC:talk';
    }

    /**
     * @return string
     */
    public function talk()
    {
        return 'InvalidTrait:talk';
    }

    /**
     * @return string
     */
    public function jump()
    {
        return 'InvalidTrait:jump';
    }

    /**
     * @return string
     */
    public function listen()
    {
        return 'InvalidTrait:listen';
    }
}
