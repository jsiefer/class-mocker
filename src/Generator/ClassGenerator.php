<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Generator;

use Zend\Code\Generator\ClassGenerator as ZendClassGenerator;
use Zend\Code\Generator\PropertyGenerator;


/**
 * Class ClassGenerator
 */
class ClassGenerator extends ZendClassGenerator
{
    /**
     * @var int
     */
    protected $_traitsIdx = 0;

    /**
     * @var array
     */
    protected $_traitMethods = [];

    /**
     * @var string[][]
     */
    protected $_magicMethods = [];

    /**
     * @return string
     */
    public function generate()
    {
        if (!empty($this->_magicMethods)) {
            $this->addProperty(
                '__magicMethodRegistry',
                $this->_magicMethods,
                PropertyGenerator::FLAG_PROTECTED | PropertyGenerator::FLAG_STATIC
            );
        }
        return parent::generate();
    }

    /**
     * @param string $extendedClass
     *
     * @return ZendClassGenerator
     */
    public function setExtendedClass($extendedClass)
    {
        $extendedClass = '\\' . trim($extendedClass, '\\');
        return parent::setExtendedClass($extendedClass);
    }

    /**
     * Use all traits
     *
     * @param \ReflectionClass[] $traits
     *
     * @return $this
     */
    public function useTraits(array $traits)
    {
        foreach ($traits as $trait) {
            $this->useTrait($trait);
        }

        return $this;
    }

    /**
     * Use a trait and save all methods so in case
     * a second trait will overwrite any
     *
     * @param \ReflectionClass $trait
     *
     * @return $this
     */
    public function useTrait(\ReflectionClass $trait)
    {
        $alias = 'trait' . ($this->_traitsIdx++);

        $this->addUse($trait->getName(), $alias);
        $this->addTrait($alias);

        foreach ($trait->getMethods() as $method) {

            switch($method->getName()) {
                case '__init':
                case '__call':
                case '__get':
                case '__set':
                    $this->registerMagicMethod($alias, $method->getName());
                    break;
                default:
                    $this->addTraitMethod($alias, $method->getName());
                    break;
            }
        }

        return $this;
    }

    /**
     * Register magic method alias
     *
     * @param string $trait
     * @param string $method
     *
     * @return void
     */
    protected function registerMagicMethod($trait, $method)
    {
        $alias = '__' . lcfirst($trait) . ucfirst(trim($method, '_'));
        $this->addTraitAlias($trait . '::' . $method, $alias);
        $this->addTraitMethod($trait, $method);

        if (!isset($this->_magicMethods[$method])) {
            $this->_magicMethods[$method] = [];
        }
        $this->_magicMethods[$method][] = $alias;
    }

    /**
     * Register trait method
     * this will overwrite all previously registered methods
     *
     * @param string $trait
     * @param string $method
     *
     * @return void
     */
    protected function addTraitMethod($trait, $method)
    {
        if (!isset($this->_traitMethods[$method])) {
            $this->_traitMethods[$method] = [];
        } else {
            foreach ($this->_traitMethods[$method] as $prefTrait) {
                $this->removeTraitOverride($prefTrait . '::' . $method);
            }
            $traits = implode(', ', $this->_traitMethods[$method]);
            $this->addTraitOverride($trait . '::' . $method, $traits);
        }
        $this->_traitMethods[$method][] = $trait;
    }




}
