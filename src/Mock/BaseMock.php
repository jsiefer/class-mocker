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
    const CONSTRUCTOR = '___construct';
    const INIT = '___init';
    const CALL = '___call';
    const SETTER = '___set';
    const GETTER = '___get';

    const DEFAULT_BEHAVIOUR_RETURN_NULL = 1;
    const DEFAULT_BEHAVIOUR_RETURN_SELF = 2;
    const DEFAULT_BEHAVIOUR_THROW_EXCEPTION = 3;

    /**
     * @var array
     */
    private $__properties = [];

    /**
     * @var \ReflectionClass
     */
    private $__reflection;

    /**
     * If in enabled, call to protected and private methods
     * are allowed
     *
     * @var bool
     */
    private $__enableClassScope = false;

    /**
     * @var int
     */
    private static $__defaultCallBehavior = self::DEFAULT_BEHAVIOUR_RETURN_NULL;

    /**
     * @var string[][]
     */
    private $_traitMethods;

    /**
     * DynamicBase constructor.
     */
    public function __construct()
    {
        $this->__callTraitMethods(self::CONSTRUCTOR, func_get_args());
        $this->__callTraitMethods(self::INIT, func_get_args());
    }

    /**
     * Set default method call behavior
     *
     * Define how the base mock should handle invalid method calls
     *
     * @param int|\Closure $behavior
     * @return void
     * @throws \InvalidArgumentException
     *
     * @see \JSiefer\ClassMocker\Mock\BaseMock::__processDefaultMethodCall
     */
    public static function setDefaultCallBehavior($behavior)
    {
        // allow custom handlers
        if ($behavior instanceof \Closure) {
            self::$__defaultCallBehavior = $behavior;
            return;
        }

        switch($behavior) {
            case self::DEFAULT_BEHAVIOUR_RETURN_NULL:
            case self::DEFAULT_BEHAVIOUR_RETURN_SELF;
            case self::DEFAULT_BEHAVIOUR_THROW_EXCEPTION:
                self::$__defaultCallBehavior = $behavior;
                return;
        }

        throw new \InvalidArgumentException("Invalid behavior option ($behavior)");
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        // first call phpMock methods
        $result = parent::__call($name, $arguments);
        if (next::isNot($result)) {
            return $result;
        }

        // check for closure functions
        $result = $this->__callClosureFunction($name, $arguments);
        if (next::isNot($result)) {
            return $result;
        }

        $result = $this->__callTraitMethods($name, $arguments);
        if (next::isNot($result)) {
            return $result;
        }

        // give any magic trait methods a chance
        $result = $this->__callTraitMethods(self::CALL, func_get_args());
        if (next::isNot($result)) {
            return $result;
        }

        // allow call to protected methods
        if (strpos($name, 'PROTECTED_') === 0) {
            $method = substr($name, 10);
            $result = $this->__callProtectedMethod($method, $arguments);
            if (next::isNot($result)) {
                return $result;
            }
        }

        /** @see \JSiefer\ClassMocker\Mock\BaseMock::__callClosureFunction() */
        if ($this->__enableClassScope) {
            $result = $this->__callProtectedMethod($name, $arguments);
            if (next::isNot($result)) {
                return $result;
            }
        }

        return $this->__processDefaultMethodCall($name, $arguments);
    }

    /**
     * Call all registered magic trait methods
     *
     * Traits can register to magic methods which will be called here
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    private function __callTraitMethods($name, $arguments)
    {
        $result = next::caller();

        $traitMethods = $this->getTraitMethods();

        if (!isset($traitMethods[$name])) {
            return $result;
        }

        $methods = $traitMethods[$name];
        $methods = array_reverse($methods);

        $scope = $this;
        $level = -1;

        $parent = function($arguments) use($scope, $methods, &$level) {
            $level++;
            if ($level >= count($methods)) {
                return next::caller();
            }
            $result = call_user_func_array([$scope, $methods[$level]], $arguments);
            $level--;
            return $result;
        };

        next::__registerParentCallback($parent);
        $result = $parent($arguments);
        next::__registerParentCallback(null);

        return $result;
    }

    /**
     * Process default method call behavior
     *
     * It is recommended to set this once during bootstrapping
     * and then stick to one process the whole time
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @throws \BadMethodCallException
     *
     * @see \JSiefer\ClassMocker\Mock\BaseMock::setDefaultCallBehavior()
     */
    private function __processDefaultMethodCall($name, $arguments)
    {
        if (self::$__defaultCallBehavior instanceof \Closure) {
            /** @var \Closure $handle */
            $handle = self::$__defaultCallBehavior;
            $handle = $handle->bindTo($this);
            return call_user_func_array($handle, $arguments);
        }

        switch(self::$__defaultCallBehavior) {
            case self::DEFAULT_BEHAVIOUR_RETURN_NULL:
                if ($name === '__toString') {
                    return '';
                }
                return null;

            case self::DEFAULT_BEHAVIOUR_RETURN_SELF:
                if ($name === '__toString') {
                    return '';
                }
                return $this;
        }

        throw new \BadMethodCallException(
            sprintf('Method %s::%s() does not exist', get_class($this), $name)
        );
    }

    /**
     * Call closure function if exist
     *
     * It is allowed to define closure functions as properties
     * which then can get called similar to stubs
     *
     * The closure function will also have access to private and
     * protected methods
     *
     * $object = new MyObject();
     * $object->sum = function($a, $b) {
     *    $this->result = $a+$b;
     *    return $this->result;
     * };
     *
     * $object->sum(5, 5); // = 10
     * $object->result; // = 10
     *
     * @param string $name
     * @param array $arguments
     *
     * @return next|mixed
     * @throws \Exception
     */
    private function __callClosureFunction($name, $arguments)
    {
        $result = next::caller();

        if (!isset($this->__properties[$name])) {
            return $result;
        }

        if (!$this->__properties[$name] instanceof \Closure) {
            return $result;
        }

        /** @var \Closure $callable */
        $callable = $this->__properties[$name];
        $callable = $callable->bindTo($this);

        $this->__enableClassScope = true;
        try {
            $result = call_user_func_array($callable, $arguments);
            $this->__enableClassScope = false;

        }
        catch(\Exception $e) {
            $this->__enableClassScope = false;
            throw $e;
        }

        return $result;
    }

    /**
     * Helper method to call a protected method
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __callProtectedMethod($name, $arguments = [])
    {
        try {
            $method = $this->__reflection()->getMethod($name);
        }
        catch(\Exception $e) {
            return next::caller();
        }

        $method->setAccessible(true);
        $result =  $method->invokeArgs($this, $arguments);
        $method->setAccessible(false);

        return $result;
    }

    /**
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        $result = $this->__callTraitMethods(self::GETTER, func_get_args());
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
        $result = $this->__callTraitMethods(self::SETTER, func_get_args());

        if (!next::isNot($result)) {
            $this->__properties[$name] = $value;
        }
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

    /**
     * Retrieve a reflection of the current class
     *
     * @return \ReflectionClass
     */
    private function __reflection()
    {
        if (!$this->__reflection) {
            $this->__reflection = new \ReflectionClass(get_class($this));
        }
        return $this->__reflection;
    }



    /**
     * Retrieve all trait methods
     *
     * @return array
     */
    private function getTraitMethods()
    {
        if (!is_array($this->_traitMethods)) {
            $this->_traitMethods = $this->mergeTraitMethods();
        }
        return $this->_traitMethods;
    }

    /**
     * Merge all trait methods from all parents
     *
     * @return array
     */
    private function mergeTraitMethods()
    {
        $reflection = $this->__reflection();
        $mergedMethods = [];
        do {
            $staticProperties = $reflection->getStaticProperties();

            if (!isset($staticProperties['___classMocker_traitMethods'])) {
                continue;
            }

            $mergedMethods = array_merge_recursive($mergedMethods, $staticProperties['___classMocker_traitMethods']);
        } while ($reflection = $reflection->getParentClass());

        return $mergedMethods;
    }
}
