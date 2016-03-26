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

use JSiefer\ClassMocker\Mock\BaseMock;
use Zend\Code\Generator\ClassGenerator as ZendClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\DocBlockReflection;


/**
 * Class ClassGenerator
 */
class ClassGenerator extends ZendClassGenerator
{
    const METHOD_TEMPLATE = 'return $this->__call("%s", func_get_args());';

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
    protected $_traitMethodsAliases = [];

    /**
     * @var \ReflectionMethod[]
     */
    protected $_method = [];

    /**
     * @return string
     */
    public function generate()
    {
        if (!empty($this->_traitMethodsAliases)) {
            $this->addProperty(
                '___classMocker_traitMethods',
                $this->_traitMethodsAliases,
                PropertyGenerator::FLAG_PRIVATE | PropertyGenerator::FLAG_STATIC
            );

            foreach (array_keys($this->_traitMethodsAliases) as $methodName) {

                switch ($methodName) {
                    case BaseMock::CALL:
                    case BaseMock::CONSTRUCTOR:
                    case BaseMock::GETTER:
                    case BaseMock::SETTER:
                    case BaseMock::INIT:
                        continue 2;
                }

                switch ('_' . $methodName) {
                    case BaseMock::CALL:
                    case BaseMock::CONSTRUCTOR:
                    case BaseMock::GETTER:
                    case BaseMock::SETTER:
                        throw new \RuntimeException(
                            sprintf(
                                "Trait method %s::%s() is not valid, use %s() instead",
                                $this->getName(),
                                $methodName,
                                '_' . $methodName
                            )
                        );
                }

                $this->generateMethod($methodName);
            }
        }
        return parent::generate();
    }

    /**
     * Generate method
     *
     * @param string $methodName
     * @return void
     */
    protected function generateMethod($methodName)
    {
        $methodReflection = $this->_method[$methodName];

        $docBlock = new DocBlockGenerator();
        $docBlock->setShortDescription("Delicate $methodName() to __call() method ");

        if ($methodReflection->getDocComment()) {
            $docBlockReflection = new DocBlockReflection($methodReflection);
            $docBlock->fromReflection($docBlockReflection);
        }

        $method = new MethodGenerator();
        $method->setName($methodName);
        $method->setDocBlock($docBlock);
        $method->setBody(sprintf(self::METHOD_TEMPLATE, $methodName));

        if ($methodReflection->isPublic()) {
            $method->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
        } else if ($methodReflection->isProtected()) {
            $method->setVisibility(MethodGenerator::VISIBILITY_PROTECTED);
        } else if ($methodReflection->isPrivate()) {
            $method->setVisibility(MethodGenerator::VISIBILITY_PRIVATE);
        }

        foreach ($methodReflection->getParameters() as $parameter) {

            $parameterGenerator = new ParameterGenerator();
            $parameterGenerator->setPosition($parameter->getPosition());
            $parameterGenerator->setName($parameter->getName());
            $parameterGenerator->setPassedByReference($parameter->isPassedByReference());

            if ($parameter->isDefaultValueAvailable()) {
                $parameterGenerator->setDefaultValue($parameter->getDefaultValue());
            }
            if ($parameter->isArray()) {
                $parameterGenerator->setType('array');
            }
            if ($typeClass = $parameter->getClass()) {
                $parameterGenerator->setType($typeClass->getName());
            }

            $method->setParameter($parameterGenerator);
        }
        $this->addMethodFromGenerator($method);
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
        $alias .= '_' . str_replace('\\', '__', $trait->getName());

        $alias = 'Trait_' . substr(md5($alias), 0, 10) . '_' .$trait->getShortName();

        $this->addUse($trait->getName(), $alias);
        $this->addTrait($alias);

        foreach ($trait->getMethods() as $method) {
            $this->registerTraitMethod($alias, $method);
        }

        return $this;
    }

    /**
     * Register magic method alias
     *
     * @param string $trait
     * @param \ReflectionMethod $method
     *
     * @return void
     */
    protected function registerTraitMethod($trait, \ReflectionMethod $method)
    {
        $name = $method->getName();
        $this->_method[$name] = $method;

        $alias = '__' . lcfirst($trait) . ucfirst($name);
        $this->addTraitAlias($trait . '::' . $name, $alias);
        $this->addTraitMethod($trait, $name);

        if (!isset($this->_traitMethodsAliases[$name])) {
            $this->_traitMethodsAliases[$name] = [];
        }
        $this->_traitMethodsAliases[$name][] = $alias;
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
