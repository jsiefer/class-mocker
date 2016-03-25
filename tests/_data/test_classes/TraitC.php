<?php

namespace JSiefer\ClassMocker\TestClasses;

use JSiefer\ClassMocker\next;


/**
 * Class TraitC
 *
 * A test trait dummy for testing trait usage
 *
 * @pattern Foobar_MyTrait
 * @sort 80
 * @package JSiefer\ClassMocker
 *
 * @property string $output
 */
trait TraitC
{


    protected function ___init()
    {
        next::parent();

        $this->output .= "!!!";
    }

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
