<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Registry;


use JSiefer\ClassMocker\Mock\BaseMock;
use JSiefer\ClassMocker\TestClasses\DummyClass;
use JSiefer\ClassMocker\TestClasses\SecondDummyClass;

/**
 * Class BaseClassRegistryTest
 *
 * @covers \JSiefer\ClassMocker\Registry\BaseClassRegistry
 */
class BaseClassRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test sorting
     *
     * @test
     */
    public function testSorting()
    {
        $registry = new BaseClassRegistry();
        $registry->register(DummyClass::class, 'MyTest_*', 10);
        $registry->register(SecondDummyClass::class, 'MyTest_Foo_*', 20);
        $registry->register(DummyClass::class, 'MyTest_*_Bar', 30);

        $this->assertEquals(DummyClass::class, $registry->find('MyTest_A'));
        $this->assertEquals(SecondDummyClass::class, $registry->find('MyTest_Foo_B'));
        $this->assertEquals(DummyClass::class, $registry->find('MyTest_Foo_B_Bar'));
        $this->assertEquals(BaseMock::class, $registry->find('Foobar'));
        $this->assertEquals(BaseMock::class, $registry->find(''));
    }

    /**
     * A base class must provider a pattern
     *
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No `pattern` defined for class
     */
    public function shouldFailOnMissingPattern()
    {
        $registry = new BaseClassRegistry();
        $registry->register(DummyClass::class);
    }

    /**
     * All base classes must register the BaseMock class
     *
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage does not extend from BaseMock
     */
    public function shouldFailOnInvalidClass()
    {
        $registry = new BaseClassRegistry();
        $registry->register(BaseClassRegistry::class);
    }
}
