<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */

namespace JSiefer\ClassMocker;

use JSiefer\ClassMocker\Mock\BaseMock;


/**
 * Class TestListenerTest
 *
 * @covers \JSiefer\ClassMocker\TestListener
 */
class TestListenerTest extends \PHPUnit_Framework_TestCase
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
     * Make sure the test listener will validate any
     * object created during a test at the end of the test
     *
     * @return void
     * @test
     */
    public function testListener()
    {
        $classMocker = new ClassMocker;
        $classMocker->mock('TestListenerTest_SomeClass');
        $classMocker->autoload('TestListenerTest_SomeClass');

        $testCase = $this->getMockForAbstractClass(\PHPUnit_Framework_TestCase::class);

        $listener = new TestListener();
        $listener->startTest($testCase);

        $testObject = new \TestListenerTest_SomeClass();
        $testObject->expects($this->once())->method('test');

        try {
            $listener->endTest($testCase, 0);
            $this->fail("Should fail");
        }
        catch(\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertStringStartsWith(
                'Expectation failed for method name is equal to',
                $e->getMessage()
            );
        }

        // everything has been reset so no error should be thrown
        $listener->endTest($testCase, 0);
    }
}
