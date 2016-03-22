<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\FrameworkMocker\Mock;

use JSiefer\ClassMocker\TestClasses\DummyClass;
use JSiefer\ClassMocker\TestClasses\DummyClassOriginal;


/**
 * Class BaseMockTest
 *
 * @covers \JSiefer\ClassMocker\Mock\BaseMock
 * @covers \JSiefer\ClassMocker\Mock\PHPUnitObject
 */
class BaseMockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testMethodCallAssertion()
    {
        $dummy = new DummyClass();
        $dummy->expects($this->once())->method('bark')->willReturn(100);

        $this->assertEquals(100, $dummy->bark());
        $this->assertNull($dummy->someMethod(1));
    }

    /**
     * @test
     */
    public function testMethodShorthand()
    {
        $dummy = new DummyClass();
        $dummy->method('bark')->willReturn(100);

        $this->assertEquals(100, $dummy->bark());
    }

    /**
     * Should forwared calls to original object
     *
     * @test
     */
    public function testProxyMode()
    {
        $original = $this->getMock('DummyClassOriginal', ['test']);
        $original->expects($this->once())->method('test')->willReturn(100);

        $dummy = new DummyClass();
        $dummy->__phpunit_setOriginalObject($original);

        $this->assertEquals(100, $dummy->test());
        $this->assertNull($dummy->test2());
    }

    /**
     * Should allow dynamic property access
     *
     * @test
     */
    public function testDynamicProperties()
    {
        $dummy = new DummyClass();
        $dummy->name = "John Snow";

        $this->assertEquals('John Snow', $dummy->name);
        $this->assertTrue(isset($dummy->name));
        $this->assertNull($dummy->age);
        $this->assertFalse(isset($dummy->age));
    }

    /**
     * Should allow static calls
     *
     * @test
     */
    public function testStaticCalls()
    {
        DummyClass::someMethod("Hello");
    }

    /**
     * Allow closures to act as implemented methods
     *
     * @test
     */
    public function testDynamicMethods()
    {
        $dummy = new DummyClass();
        $dummy->myName = "John Snow";
        $dummy->rename = function($name)
        {
            $this->myName = $name;
        };
        $dummy->rename('Foobar');

        $this->assertEquals('Foobar', $dummy->myName);
    }

    /**
     * Allow call to protected methods by using the 'PROTECTED_' prefix
     * 
     * @return void
     * @test
     */
    public function testProtectedMethodCall()
    {
        $dummy = new DummyClass();

        $this->assertNull($dummy->secret(10));
        $this->assertEquals(20, $dummy->PROTECTED_secret(10));
    }
}
