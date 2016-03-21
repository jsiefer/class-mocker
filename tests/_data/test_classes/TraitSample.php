<?php

namespace JSiefer\ClassMocker\TestClasses;


/**
 * Class TraitSample
 *
 * A test trait dummy for testing trait usage
 *
 * @package JSiefer\ClassMocker
 */
trait TraitSample
{
    /**
     * @return string
     */
    public function talk()
    {
        return 'TraitC:talk';
    }

    /**
     * @return string
     */
    public function jump()
    {
        return 'TraitC:jump';
    }

    /**
     * @return string
     */
    public function listen()
    {
        return 'TraitC:listen';
    }
}
