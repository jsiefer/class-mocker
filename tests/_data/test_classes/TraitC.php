<?php

namespace JSiefer\ClassMocker\TestClasses;

use JSiefer\ClassMocker\next;


/**
 * Class TraitC
 *
 * A test trait dummy for testing trait usage
 *
 * @pattern Foobar\BaseClass
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
     * @param $what
     * @param int $volume
     *
     * @param Human $target
     *
     * @return string
     */
    public function talk($what, $volume = 100, Human &$target = null)
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
