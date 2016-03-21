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

/**
 * Class BaseMock
 */
abstract class BaseMock extends PHPUnitObject
{
    const CONSTRUCTOR = '__construct';
    const INIT = '__init';
    const CALL = '__call';
    const SETTER = '__set';
    const GETTER = '__get';

    /**
     * @var array
     */
    private $__properties = [];

    /**
     * @var string[][]
     */
    protected static $__magicMethodRegistry = [];

    /**
     * DynamicBase constructor.
     */
    public function __construct()
    {
        $this->callMagicMethods(self::CONSTRUCTOR, func_get_args());
        $this->callMagicMethods(self::INIT, func_get_args());
        $this->__call('__construct', func_get_args());
    }

    /**
     * Call all registered magic trait methods
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    private function callMagicMethods($name, $arguments)
    {
        $result = next::caller();

        if (!isset(static::$__magicMethodRegistry[$name])) {
            return $result;
        }

        foreach (static::$__magicMethodRegistry[$name] as $method) {
            $result = call_user_func_array([$this, $method], $arguments);
            if (next::isNot($result)) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        // check if we have a closure as property
        if (isset($this->__properties[$name]) && $this->__properties[$name] instanceof \Closure) {
            $callable = $this->__properties[$name]->bindTo($this);
            return call_user_func_array($callable, $arguments);
        }

        // give any magic trait methods a chance
        $result = $this->callMagicMethods(self::CALL, func_get_args());
        if (next::isNot($result)) {
            return $result;
        }

        return parent::__call($name, $arguments);
    }

    /**
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        $result = $this->callMagicMethods(self::GETTER, func_get_args());
        if (next::isNot($result)) {
            return $result;
        }

        if (isset($this->__properties[$name])) {
            return $this->__properties[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->callMagicMethods(self::SETTER, func_get_args());
        $this->__properties[$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->__properties[$name]);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return void
     */
    public static function __callStatic($name, $arguments)
    {
        return;
    }

}
