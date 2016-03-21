<?php

namespace JSiefer\ClassMocker\TestClasses;

use JSiefer\ClassMocker\next;


/**
 * Class TraitB
 *
 * A test trait dummy for testing trait usage
 *
 * @pattern Foobar_MyTrait
 * @sort 90
 * @package JSiefer\ClassMocker
 */
trait TraitB
{

    protected function __init()
    {
        $this->output .= "World";
        return next::caller();
    }

    /**
     * @return string
     */
    public function hide()
    {
        return 'TraitB:hide';
    }

    /**
     * @return string
     */
    public function show()
    {
        return 'TraitB:show';
    }

    /**
     * @return string
     */
    public function jump()
    {
        return 'TraitB:jump';
    }
}
