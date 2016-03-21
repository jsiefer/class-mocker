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

use JSiefer\ClassMocker\Reflection\TraitReflection;
use JSiefer\ClassMocker\Utils\SortUtils;

/**
 * Class TraitRegistry
 */
class TraitRegistry
{
    /**
     * Registered traits that we will append
     *
     * @var TraitReflection[]
     */
    protected $_traits = [];

    /**
     * Register trait
     *
     * @param string $trait
     * @param string $pattern
     * @param float $sort
     *
     * @return TraitReflection
     */
    public function register($trait, $pattern = null, $sort = null)
    {
        $reflection = new TraitReflection($trait, $pattern, $sort);

        if (!$reflection->getPattern()) {
            throw new \RuntimeException("No `pattern` defined for trait '$trait'");
        }
        $this->_traits[] = $reflection;

        return $reflection;
    }

    /**
     * Retrieve all traits by matching className
     *
     * @param string $className
     * @return TraitReflection[]
     */
    public function findByClass($className)
    {
        $result = [];
        foreach ($this->_traits as $trait) {
            if ($trait->matchClassName($className)) {
                $result[] = $trait;
            }
        }
        $result = SortUtils::sortReflections($result);

        return $result;
    }
}
