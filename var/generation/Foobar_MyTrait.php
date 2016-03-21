<?php
/**
 * Auto generated file by ClassMocker, do not change
 *
 * @author ClassMocker
 * @mock
 */


use JSiefer\ClassMocker\Test\Dummy\TraitA as trait0;
use JSiefer\ClassMocker\Test\Dummy\TraitB as trait1;
use JSiefer\ClassMocker\Test\Dummy\TraitC as trait2;

class Foobar_MyTrait extends \JSiefer\ClassMocker\Mock\BaseMock
{

    use trait0, trait1, trait2 {
        trait0::__init as __trait0Init;
        trait0::__call as __trait0Call;
        trait1::__init as __trait1Init;
        trait2::__init as __trait2Init;
        trait1::hide insteadof trait0;
        trait1::show insteadof trait0;
        trait2::__init insteadof trait0, trait1;
        trait2::talk insteadof trait0;
        trait2::jump insteadof trait1;

    }

    protected static $__magicMethodRegistry = array(
        '__init' => array(
            '__trait0Init',
            '__trait1Init',
            '__trait2Init',
        ),
        '__call' => array(
            '__trait0Call',
        ),
    );


}

