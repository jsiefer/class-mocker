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
use PHPUnit_Framework_Test;

/**
 * Class TestListener
 */
class TestListener extends \PHPUnit_Framework_BaseTestListener
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

        parent::startTest($test);
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

        parent::endTest($test, $time);
    }
}
