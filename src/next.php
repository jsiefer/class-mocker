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


/**
 * Value Constant Object
 *
 * @method static next caller()
 * @method static next setter()
 * @method static next getter()
 *
 * @package JSiefer\ClassMocker
 */
class next
{
    private static $instance;

    /**
     * @var \Closure
     */
    private static $activeTraitMethod;

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isNot($value)
    {
        return !($value instanceof self);
    }

    /**
     * @return next
     */
    protected static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return next
     */
    public static function __callStatic($name, $arguments)
    {
        return self::getInstance();
    }

    /**
     * Allows call to parent trait method if it got overwritten
     *
     * @return mixed
     *
     * @see \JSiefer\ClassMocker\Mock\BaseMock::__callTraitMethods()
     */
    public static function parent()
    {
        if (!self::$activeTraitMethod) {
            throw new \BadMethodCallException("next:parent() call is only allowed in trait calls");
        }
        $arguments = func_get_args();

        return call_user_func(self::$activeTraitMethod, $arguments);
    }

    /**
     * Register or un-register any active parent callbacks
     *
     * @param null $callback
     * @return void
     *
     * @see \JSiefer\ClassMocker\Mock\BaseMock::__callTraitMethods()
     */
    public static function __registerParentCallback($callback = null)
    {
        self::$activeTraitMethod = $callback;
    }
}
