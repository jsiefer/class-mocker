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

use Exception;
use JSiefer\ClassMocker\Mock\BaseMock;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;

/**
 * Class TestListener
 */
class TestListener implements \PHPUnit_Framework_TestListener
{
    /**
     * Registered mocks
     *
     * @var BaseMock[]
     */
    private $_mocks = [];

    /**
     * @param BaseMock $mock
     */
    public function registerMock(BaseMock $mock)
    {
        $this->_mocks[] = $mock;
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        BaseMock::__classMock_registerListener($this);
        $this->_mocks = [];
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        foreach ($this->_mocks as $mock) {
            if ($mock->__phpunit_hasMatchers() && $test instanceof \PHPUnit_Framework_TestCase) {
                $test->addToAssertionCount(1);
            }
            $mock->__phpunit_verify();
        }

        BaseMock::__classMock_unregisterListener();
        $this->_mocks = [];
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {}

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {}

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {}

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     *
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {}

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {}

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {}

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     *
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {}
}
