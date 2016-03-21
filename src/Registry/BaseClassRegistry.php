<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Registry;


use JSiefer\ClassMocker\Mock\BaseMock;
use JSiefer\ClassMocker\Reflection\ClassReflection;
use JSiefer\ClassMocker\Utils\SortUtils;


/**
 * Class BaseClassRegistry
 */
class BaseClassRegistry
{
    /**
     * Registered classes that will be used for certain mocks
     *
     * @var ClassReflection[]
     */
    protected $_classes = [];

    /**
     * Register class
     *
     * @param $class
     * @param string $pattern
     * @param float $sort
     *
     * @return ClassReflection
     */
    public function register($class, $pattern = null, $sort = null)
    {
        $reflection = new ClassReflection($class, $pattern, $sort);

        if (!$reflection->isSubclassOf(BaseMock::class)) {
            throw new \RuntimeException("Mock classes '$class' does not extend from BaseMock");
        }

        if (!$reflection->getPattern()) {
            throw new \RuntimeException("No `pattern` defined for class '$class'");
        }
        $this->_classes[] = $reflection;

        return $reflection;
    }

    /**
     * Find the best matching base/parent class for a className
     *
     * @param string $className
     * @return string
     */
    public function find($className)
    {
        if (empty($className)) {
            return BaseMock::class;
        }

        /** @var ClassReflection[] $result */
        $result = [];
        foreach ($this->_classes as $class) {
            if ($class->matchClassName($className)) {
                $result[] = $class;
            }
        }
        $result = SortUtils::sortReflections($result);

        if (count($result)) {
            return $result[0]->getName();
        }
        return BaseMock::class;
    }
}
