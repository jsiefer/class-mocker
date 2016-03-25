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
abstract class PHPUnitObject implements PHPUnitObjectInterface
{
    /**
     * Invocation Mocker
     *
     * @var InvocationMocker
     */
    private $__phpunit_invocationMocker;

    /**
     * Orignal object for proxy mode
     *
     * @var mixed
     */
    private $__phpunit_originalObject;

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
        $mocker = $this->__phpunit_getInvocationMocker();
        $invocation = new InvocationObject(get_class($this), $name, $arguments, $this, FALSE);

        $result = $mocker->invoke($invocation);

        // check for proxy method
        if ($this->__phpunit_originalObject) {
            if (method_exists($this->__phpunit_originalObject, $name)) {
                return call_user_func_array(array($this->__phpunit_originalObject, $name), $arguments);
            }
        }

        return $result;
    }

    /**
     * Registers a new expectation in the mock object and returns the match
     * object which can be infused with further details.
     *
     * @param  \PHPUnit_Framework_MockObject_Matcher_Invocation       $matcher
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    public function expects(\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
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
     * @param $originalObject
     *
     * @return InvocationMocker
     * @since  Method available since Release 2.0.0
     */
    public function __phpunit_setOriginalObject($originalObject)
    {
        $this->__phpunit_originalObject = $originalObject;
    }

    /**
     * @return InvocationMocker
     */
    public function __phpunit_getInvocationMocker()
    {
        if ($this->__phpunit_invocationMocker === null) {
            $this->__phpunit_invocationMocker = new InvocationMocker();
            if (self::$__classMock_activeListener) {
                self::$__classMock_activeListener->registerMock($this);
            }
        }

        return $this->__phpunit_invocationMocker;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function __phpunit_hasMatchers()
    {
        return $this->__phpunit_getInvocationMocker()->hasMatchers();
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws \PHPUnit_Framework_ExpectationFailedException
     * @codeCoverageIgnore
     */
    public function __phpunit_verify()
    {
        $this->__phpunit_getInvocationMocker()->verify();
        $this->__phpunit_invocationMocker = null;
    }

    /**
     * Registers a new static expectation in the mock object and returns the
     * match object which can be infused with further details.
     *
     * @param  \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     * @codeCoverageIgnore
     */
    public static function staticExpects(\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        throw new \BadMethodCallException("Method only implemented for backward compatibility");
    }

    /**
     * @return \PHPUnit_Framework_MockObject_InvocationMocker
     * @codeCoverageIgnore
     */
    public static function __phpunit_getStaticInvocationMocker()
    {
        throw new \BadMethodCallException("Method only implemented for backward compatibility");
    }
}
