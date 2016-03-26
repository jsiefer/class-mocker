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


use JSiefer\ClassMocker\Footprint\ClassFootprint;
use JSiefer\ClassMocker\Generator\FileGenerator;
use JSiefer\ClassMocker\Generator\FileGeneratorBuilder;
use JSiefer\ClassMocker\Reflection\ClassReflection;
use JSiefer\ClassMocker\Reflection\TraitReflection;


/**
 * Class ClassMocker
 */
class ClassMocker
{
    /**
     * The register class name patterns that we will get mock
     * at any time
     *
     * @var string[]
     */
    protected $_mockPatterns = [];

    /**
     * The register class name patterns that we will get mock
     * if no other autoload claims them
     *
     * @var string[]
     */
    protected $_optionalMockPatterns = [];

    /**
     * File Generator Builder
     *
     * @var FileGeneratorBuilder
     */
    protected $_builder;

    /**
     * The directory where save the generated files
     *
     * @var string
     */
    protected $_generationDir;

    /**
     * ClassMocker constructor.
     */
    public function __construct()
    {
        $this->_builder = new FileGeneratorBuilder();
    }

    /**
     * Enable class mocker by registering the auto loader
     *
     * @return $this
     */
    public function enable()
    {
        spl_autoload_register([$this, 'autoload'], true, true);
        spl_autoload_register([$this, 'autoloadOptional'], true, false);
        return $this;
    }

    /**
     * Disable class mocker by un-registering the auto loader
     *
     * @return $this
     */
    public function disable()
    {
        spl_autoload_unregister([$this, 'autoload']);
        spl_autoload_unregister([$this, 'autoloadOptional']);
        return $this;
    }

    /**
     * Register an entire framework mock
     *
     * @param FrameworkInterface $framework
     *
     * @return $this
     */
    public function mockFramework(FrameworkInterface $framework)
    {
        $framework->register($this);

        return $this;
    }

    /**
     * Mock any class matching the given pattern
     *
     * e.g.
     * mock('Mage*')
     * mock('Mage*Collection')
     * mock('Foo\Bar\*')
     *
     * @param string $pattern
     * @param bool $ifNotExist
     *
     * @return $this
     */
    public function mock($pattern, $ifNotExist = false)
    {
        if ($ifNotExist) {
            $this->_optionalMockPatterns[] = $pattern;
        } else {
            $this->_mockPatterns[] = $pattern;
        }

        return $this;
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
        $this->_builder->importFootprints($file);
        return $this;
    }

    /**
     * Register class footprint
     *
     * @param $className
     * @param ClassFootprint $footprint
     *
     * @return $this
     */
    public function registerFootprint($className, ClassFootprint $footprint)
    {
        $this->_builder->registerFootprint($className, $footprint);
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
        return $this->_builder->registerTrait($trait, $pattern, $sort);
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
        return $this->_builder->registerBaseClass($class, $pattern, $sort);
    }

    /**
     * Autoload handler for PHP
     *
     * @param string $className
     *
     * @return bool
     */
    public function autoload($className)
    {
        return $this->_autoload($this->_mockPatterns, $className);
    }

    /**
     * Autoload handler for PHP
     *
     * @param string $className
     *
     * @return bool
     */
    public function autoloadOptional($className)
    {
        return $this->_autoload($this->_optionalMockPatterns, $className);
    }

    /**
     * Generate and load the given class
     *
     * @param string $className
     *
     * @throws \Exception
     * @return void
     */
    public function generateAndLoadClass($className)
    {
        if (class_exists($className, false)) {
            throw new \RuntimeException("Unable to generate and load already existing class '$className'");
        }

        $filename = $this->findFile($className);

        if (!$filename || !file_exists($filename)) {
            $classFileGenerator = $this->_builder->build($className);

            if ($filename) {
                file_put_contents($filename, $classFileGenerator->generate());
            } else {
                $this->evalContent($classFileGenerator);
            }
        }
        if ($filename && file_exists($filename)) {
            include $filename;
        }
    }

    /**
     * Autoload class if matching any of given patterns
     *
     * @param string[] $patterns
     * @param string $className
     *
     * @return bool
     */
    protected function _autoload($patterns, $className)
    {
        foreach ($patterns as $pattern) {
            if (!fnmatch($pattern, $className, FNM_NOESCAPE)) {
                continue;
            }
            $this->generateAndLoadClass($className);
            return true;

        }
        return false;
    }

    /**
     * Eval file content
     *
     * @param FileGenerator $classFileGenerator
     */
    private function evalContent(FileGenerator $classFileGenerator)
    {
        $code = $classFileGenerator->generate();
        $code = substr($code, 6); // remove <?php

        eval($code);
    }

    /**
     * Retrieve file for class name
     *
     * @param string $className
     *
     * @return string
     * @throws \Exception
     */
    protected function findFile($className)
    {
        $genDir = $this->getGenerationDir();

        if (!$genDir) {
            return null;
        }

        $path = [$genDir];
        $path[] =  str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

        $path = implode(DIRECTORY_SEPARATOR, $path);

        $dir = dirname($path);

        if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
            $e = error_get_last();
            throw new \RuntimeException(
                "Failed to create class generation folder: " . $e['message']
            );
        }

        return $path;
    }

    /**
     * @return string
     */
    public function getGenerationDir()
    {
        return $this->_generationDir;
    }

    /**
     * Define a generation dir to save all generated files
     *
     * @param string $generationDir
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setGenerationDir($generationDir)
    {
        $this->_generationDir = $generationDir;
        return $this;
    }
}
