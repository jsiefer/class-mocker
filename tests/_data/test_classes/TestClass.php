<?php

namespace JSiefer\ClassMocker\TestClasses;

/**
 * Class TestClass
 *
 * A test class that extends non-existing classes
 */
class TestClass
    extends \Foo_Bar
    implements \Foo_Bar_Interface
{

    public function speak()
    {
        echo "hi!";
    }

}
