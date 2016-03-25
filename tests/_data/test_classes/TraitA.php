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
 *
 * @method string getFoobar()
 * @property string $foobar
 * @property string $output
 */
trait TraitA
{


    protected function ___init($name = '')
    {
        $this->output = "Hello " . $name;
        return next::parent();
    }


    public function ___call($name, $arguments)
    {
        if ($name == 'getFoobar') {
            return true;
        }

        return next::parent($name, $arguments);
    }

    public function ___get($name)
    {
        if ($name == 'foobar') {
            return 'test';
        }

        return next::parent($name);
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
