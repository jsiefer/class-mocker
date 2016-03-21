<?php
namespace JSiefer\ClassMocker\TestFramework\Data;

use JSiefer\ClassMocker\TestFramework\InterfaceB;

class ObjectA implements InterfaceA, InterfaceB
{
    const EVENT = 'foobar';
    const SORT = 100;
}
