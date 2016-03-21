<?php

namespace JSiefer\ClassMocker\TestClasses;

use JSiefer\ClassMocker\next;

/**
 * Class TraitA
 *
 * A test trait dummy for testing trait usage
 *
 * @pattern Foobar_MyTrait
 * @sort 100
 * @package JSiefer\ClassMocker
 */
trait TraitA
{


    protected function __init()
    {
        $this->output .= "Hello";
        return next::caller();
    }


    public function __call($name, $arguments)
    {
        if($name == 'getFoobar') {
            return true;
        }
        return next::caller();
    }


    /**
     * @return string
     */
    public function talk()
    {
        return 'TraitA:talk';
    }

    /**
     * @return string
     */
    public function hide()
    {
        return 'TraitA:hide';
    }

    /**
     * @return string
     */
    public function show()
    {
        return 'TraitA:show';
    }

    /**
     * @return string
     */
    public function read()
    {
        return 'TraitA:read';
    }
}
