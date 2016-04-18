<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Reflection;

use JSiefer\ClassMocker\TestClasses\DummyClass;
use JSiefer\ClassMocker\TestClasses\SecondDummyClass;
use JSiefer\ClassMocker\TestClasses\ThirdDummyClass;


/**
 * Class ClassReflectionTest
 *
 * @covers \JSiefer\ClassMocker\Reflection\ClassReflection
 */
class ClassReflectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Class Reflection
     *
     * @test
     */
    public function testReflection()
    {
        $reflection = new ClassReflection(DummyClass::class);
        $this->assertEquals('', $reflection->getPattern());
        $this->assertEquals(0, $reflection->getSort());

        $reflection = new ClassReflection(SecondDummyClass::class);
        $this->assertEquals('MyClass_*', $reflection->getPattern());
        $this->assertEquals(0, $reflection->getSort());

        $reflection = new ClassReflection(ThirdDummyClass::class);
        $this->assertEquals('', $reflection->getPattern());
        $this->assertEquals(0, $reflection->getSort());
    }

    /**
     * Test match class name
     *
     * @test
     */
    public function testMatchName()
    {
        $reflection = new ClassReflection(DummyClass::class);
        $reflection->setPattern('Foo*Bar');
        $reflection->setSort(100);

        $this->assertTrue($reflection->matchClassName('FooBar'));
        $this->assertTrue($reflection->matchClassName('Foo_Bar'));
        $this->assertTrue($reflection->matchClassName('FooSampleBar'));

        $this->assertFalse($reflection->matchClassName('Foo'));
        $this->assertFalse($reflection->matchClassName('FooBarTest'));
        $this->assertFalse($reflection->matchClassName('TestFooBar'));
    }
}
