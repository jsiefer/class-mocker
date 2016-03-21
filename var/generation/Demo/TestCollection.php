<?php
/**
 * Auto generated file by ClassMocker, do not change
 *
 * @author ClassMocker
 * @mock
 */


namespace Demo;

use JSiefer\ClassMocker\Test\Dummy\TraitC as trait0;
use JSiefer\ClassMocker\Test\Dummy\TraitB as trait1;
use JSiefer\ClassMocker\Test\Dummy\DummyTrait as trait2;
use JSiefer\ClassMocker\Test\Dummy\TraitA as trait3;

class TestCollection extends \JSiefer\ClassMocker\Mock\BaseMock
{

    use trait0, trait1, trait2, trait3 {
        trait0::__init as __trait0Init;
        trait1::__init as __trait1Init;
        trait3::__init as __trait3Init;
        trait3::__call as __trait3Call;
        trait1::jump insteadof trait0;
        trait3::__init insteadof trait0, trait1;
        trait3::talk insteadof trait0, trait2;
        trait3::hide insteadof trait1;
        trait3::show insteadof trait1;

    }

    protected static $__magicMethodRegistry = array(
        '__init' => array(
            '__trait0Init',
            '__trait1Init',
            '__trait3Init',
        ),
        '__call' => array(
            '__trait3Call',
        ),
    );


}

