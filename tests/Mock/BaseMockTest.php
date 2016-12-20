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

use JSiefer\ClassMocker\Mock\BaseMock;
use JSiefer\ClassMocker\next;
use JSiefer\ClassMocker\TestClasses\DummyClass;
use JSiefer\ClassMocker\TestClasses\DummyClassOriginal;


/**
 * Class BaseMockTest
 *
 * @covers \JSiefer\ClassMocker\Mock\BaseMock
 * @covers \JSiefer\ClassMocker\Mock\PHPUnitObject
 * @covers \JSiefer\ClassMocker\Mock\InvocationMocker
 */
class BaseMockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        BaseMock::setDefaultCallBehavior(BaseMock::DEFAULT_BEHAVIOUR_RETURN_NULL);
    }

    /**
     * @test
     */
    public function testMethodCallAssertion()
    {
        $dummy = new DummyClass();
        $dummy->expects($this->once())->method('bark')->will($this->returnValue(100));

        $this->assertEquals(100, $dummy->bark());
        $this->assertNull($dummy->someMethod(1));


        $testException = new \Exception('foobar');

        $dummy->expects($this->any())->method('walk')->will($this->returnValue(100));
        $dummy->expects($this->any())->method('walk')->will($this->throwException($testException));

        try {
            $dummy->walk();
            $this->fail("Should have thrown exception");
        }
        catch(\Exception $e) {
            $this->assertSame($testException, $e);
        }

    }

    /**
     * @test
     */
    public function testMethodShorthand()
    {
        $dummy = new DummyClass();
        $dummy->method('bark')->will($this->returnValue(100));

        $this->assertEquals(100, $dummy->bark());
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
        $testCase = $this;

        $dummy = new DummyClass();
        $dummy->myName = "John Snow";

        // should not try to call property
        $testCase->assertNull($dummy->myName());

        /**
         * Method should have access to all
         * public and private class methods
         */
        $dummy->rename = function($name = null) use ($testCase)
        {
            if ($name === null) {
                return next::caller();
            }

            if (empty($name)) {
                throw new \InvalidArgumentException('name may not be empty');
            }

            $this->myName = $name;

            // should have access to private/protected methods
            $testCase->assertEquals(20, $this->protectedMethod(10));
            $testCase->assertEquals(20, $this->privateMethod(10));
            $testCase->assertEquals(20, $this->publicMethod(10));

            $testCase->assertNull($this->protectedMethod(0));
        };

        $dummy->rename('Foobar');
        $this->assertEquals('Foobar', $dummy->myName);

        //should stay private
        $testCase->assertNull($dummy->privateMethod(10));

        try {
            $dummy->rename('');
            $this->fail('Expected exception to test resetting class scope');
        }
        catch(\InvalidArgumentException $e) {
            // should still be private
            $testCase->assertNull($dummy->privateMethod(10));
        }

        // allow fall-through to default behavior using return next::caller()
        BaseMock::setDefaultCallBehavior(BaseMock::DEFAULT_BEHAVIOUR_RETURN_SELF);
        $this->assertSame($dummy, $dummy->rename());
    }

    /**
     * Allow call to protected methods by using the 'PROTECTED_' prefix
     * and the __callProtectedMethod
     *
     * @return void
     * @test
     */
    public function testProtectedMethodCall()
    {
        $dummy = new DummyClass();

        $this->assertNull($dummy->protectedMethod(10));
        $this->assertNull($dummy->privateMethod(10));
        $this->assertEquals(20, $dummy->publicMethod(10));

        $this->assertEquals(20, $dummy->__callProtectedMethod('protectedMethod', [10]));
        $this->assertEquals(20, $dummy->__callProtectedMethod('privateMethod', [10]));
        $this->assertEquals(20, $dummy->__callProtectedMethod('publicMethod', [10]));

        $this->assertNull($dummy->protectedMethod(10));
        $this->assertNull($dummy->privateMethod(10));
        $this->assertEquals(20, $dummy->publicMethod(10));

        $this->assertSame(next::caller(), $dummy->__callProtectedMethod('notExist'));
        $this->assertNull($dummy->PROTECTED_notExist());
    }

    /**
     * Call to __toString() should return string
     *
     * @return void
     * @test
     */
    public function testToStringMethod()
    {
        $dummy = new DummyClass();

        $this->assertNull($dummy->someMethod());
        $this->assertSame('', $dummy->__toString());
    }

    /**
     * Test to make sure you can create a mock
     * of a class that extends from BaseMock
     *
     * @return void
     * @test
     */
    public function testMockOfBaseMock()
    {
        $mock = $this->getMock(DummyClass::class);
    }


    /**
     * Test different default behaviors
     *
     * @return void
     * @test
     */
    public function testDefaultMethodCallBehavior()
    {
        $dummy = new DummyClass();
        $dummy->foobar = 10;

        // return null for none defined methods
        BaseMock::setDefaultCallBehavior(BaseMock::DEFAULT_BEHAVIOUR_RETURN_NULL);
        $this->assertNull($dummy->someMethod(10));
        $this->assertSame('', $dummy->__toString());

        // return self for none defined methods
        BaseMock::setDefaultCallBehavior(BaseMock::DEFAULT_BEHAVIOUR_RETURN_SELF);
        $this->assertSame($dummy, $dummy->someMethod(10));
        $this->assertSame('', $dummy->__toString());

        // call handle for none defined methods
        BaseMock::setDefaultCallBehavior(
            function($a) {
                return $this->foobar + $a;
            }
        );
        $this->assertEquals(20, $dummy->someMethod(10));

        // throw exception for none defined methods
        BaseMock::setDefaultCallBehavior(BaseMock::DEFAULT_BEHAVIOUR_THROW_EXCEPTION);
        try {
            $dummy->someMethod(10);
            $this->fail("Should throw error on invalid method call");
        }
        catch(\BadMethodCallException $e) {
            $this->assertEquals(
                'Method JSiefer\ClassMocker\TestClasses\DummyClass::someMethod() does not exist',
                $e->getMessage()
            );
        }

        try {
            BaseMock::setDefaultCallBehavior('Something Invalid');
            $this->fail("Should throw error on invalid behavior option");
        }
        catch(\InvalidArgumentException $e) {
            $this->assertEquals(
                'Invalid behavior option (Something Invalid)',
                $e->getMessage()
            );
        }

        BaseMock::setDefaultCallBehavior(BaseMock::DEFAULT_BEHAVIOUR_RETURN_NULL);
    }
}
