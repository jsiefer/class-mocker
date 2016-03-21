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


use JSiefer\ClassMocker\Reflection\ClassReflection;
use JSiefer\ClassMocker\Reflection\TraitReflection;
use Zend\Code\Generator\InterfaceGenerator;
use JSiefer\ClassMocker\Registry\BaseClassRegistry;
use JSiefer\ClassMocker\Registry\FootprintRegistry;
use JSiefer\ClassMocker\Registry\TraitRegistry;

/**
 * Class MockClassGenerator
 */
class FileGeneratorBuilder
{
    /**
     * Registered traits that we will append
     *
     * @var TraitRegistry
     */
    protected $_traitRegistry;

    /**
     * Registered classes that will be used for certain mocks
     *
     * @var BaseClassRegistry
     */
    protected $_baseClassRegistry;

    /**
     * Registered class footprints
     *
     * @var FootprintRegistry
     */
    protected $_footprintRegistry;

    /**
     * MockClassGenerator constructor.
     */
    public function __construct()
    {
        $this->_baseClassRegistry = new BaseClassRegistry();
        $this->_traitRegistry = new TraitRegistry();
        $this->_footprintRegistry = new FootprintRegistry();
    }

    /**
     * Register reference
     *
     * A reference is a json file that contains some basic class footprints
     * like constants and interface/parents
     *
     * @param string $file
     *
     * @return $this
     */
    public function importFootprints($file)
    {
        $this->_footprintRegistry->import($file);
        return $this;
    }

    /**
     * Register trait
     *
     * @param string $trait
     * @param string $pattern
     * @param float $sort
     *
     * @return TraitReflection
     */
    public function registerTrait($trait, $pattern = null, $sort = null)
    {
        return $this->_traitRegistry->register($trait, $pattern, $sort);
    }

    /**
     * Register base class
     *
     * @param $class
     * @param string $pattern
     * @param float $sort
     *
     * @return ClassReflection
     */
    public function registerBaseClass($class, $pattern = null, $sort = null)
    {
        return $this->_baseClassRegistry->register($class, $pattern, $sort);
    }

    /**
     * Generate class using any given references or traits
     *
     * @param string $className
     *
     * @return FileGenerator
     */
    public function build($className)
    {
        $footprint = $this->_footprintRegistry->get($className);
        $parentClass = $this->_baseClassRegistry->find($className);

        if ($footprint->isInterface()) {
            $generator = new InterfaceGenerator();
        } else {
            $traits = $this->_traitRegistry->findByClass($className);

            $generator = new ClassGenerator();
            $generator->useTraits($traits);
        }

        $generator->setName($className);
        $generator->setExtendedClass($parentClass);
        $generator->setImplementedInterfaces($footprint->getInterfaces());

        foreach ($footprint->getConstants() as $name => $value) {
            $generator->addConstant($name, $value);
        }

        $file = new FileGenerator();
        $file->setClass($generator);

        return $file;
    }
}
