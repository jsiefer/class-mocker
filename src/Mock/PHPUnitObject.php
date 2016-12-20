<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Mock;

use JSiefer\ClassMocker\next;
use JSiefer\ClassMocker\TestListener;
use PHPUnit_Framework_MockObject_Invocation_Object as InvocationObject;
use PHPUnit_Framework_MockObject_MockObject as PHPUnitObjectInterface;

/**
 * Class PHPUnitObject
 */
abstract class PHPUnitObject
{
    /**
     * Invocation Mocker
     *
     * @var InvocationMocker
     */
    private $__classMock_invocationMocker;

    /**
     * Current active test case
     *
     * @var TestListener
     */
    private static $__classMock_activeListener;

    /**
     * Register active listener
     *
     * @param TestListener $listener
     * @return void
     *
     * @see TestListener
     * @codeCoverageIgnore
     */
    public static function __classMock_registerListener(TestListener $listener)
    {
        self::$__classMock_activeListener = $listener;
    }

    /**
     * Un-register Listener
     *
     * @return void
     *
     * @see TestListener
     * @codeCoverageIgnore
     */
    public static function __classMock_unregisterListener()
    {
        self::$__classMock_activeListener = null;
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $mocker = $this->__classMock_getInvocationMocker();
        $invocation = new InvocationObject(get_class($this), $name, $arguments, $this, FALSE);

        $result = $mocker->invoke($invocation);

        return $result;
    }

    /**
     * Registers a new expectation in the mock object and returns the match
     * object which can be infused with further details.
     *
     * @param  \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    public function expects(\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->__classMock_getInvocationMocker()->expects($matcher);
    }

    /**
     * @param $constraint
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function method($constraint)
    {
        $any = new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
        $expects = $this->expects($any);
        return call_user_func_array(array($expects, 'method'), func_get_args());
    }

    /**
     * @return InvocationMocker
     */
    public function __classMock_getInvocationMocker()
    {
        if ($this->__classMock_invocationMocker === null) {
            $this->__classMock_invocationMocker = new InvocationMocker();
            if (self::$__classMock_activeListener) {
                self::$__classMock_activeListener->registerMock($this);
            }
        }

        return $this->__classMock_invocationMocker;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function __classMock_hasMatchers()
    {
        return $this->__classMock_getInvocationMocker()->hasMatchers();
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws \PHPUnit_Framework_ExpectationFailedException
     * @codeCoverageIgnore
     */
    public function __classMock_verify()
    {
        $this->__classMock_getInvocationMocker()->verify();
        $this->__classMock_invocationMocker = null;
    }
}
