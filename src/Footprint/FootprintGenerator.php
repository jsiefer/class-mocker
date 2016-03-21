<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Footprint;


use Zend\Code\Scanner\ClassScanner;
use Zend\Code\Scanner\DirectoryScanner as DirectoryCodeScanner;


/**
 * Class FootprintGenerator
 *
 * Simple footprint generator for a directory
 *
 * Depending of the size of the directory you want to scan you may
 * need to increase the memory limit
 *
 * e.g. ini_set('memory_limit', '2G');
 */
class FootprintGenerator
{
    /**
     * Scanner to use
     *
     * @var DirectoryCodeScanner
     */
    protected $_scanner;

    /**
     * ReferenceGenerator constructor.
     *
     * @param string $directory
     * @param DirectoryCodeScanner $scanner
     */
    public function __construct($directory = null, DirectoryCodeScanner $scanner = null)
    {
        $this->_scanner = $scanner;

        if (!empty($directory)) {
            $this->addDirectory($directory);
        }
    }

    /**
     * Add directory to scan
     *
     * @param string $dir
     *
     * @return $this
     */
    public function addDirectory($dir)
    {
        $scanner = $this->getScanner();
        $scanner->addDirectory($dir);

        return $this;
    }

    /**
     * Create JSON reference from given directories
     *
     * @return string
     */
    public function generate()
    {
        $reference = [];

        $scanner = $this->getScanner();

        /** @var  $class ClassScanner */
        foreach ($scanner->getClasses() as $class) {
            $footprint = $this->getClassFootprint($class);
            $reference[$class->getName()] = $footprint->export();
        }
        $reference = json_encode($reference);

        return $reference;
    }

    /**
     * Retrieve class footprint from class scanner
     *
     * @param ClassScanner $class
     *
     * @return ClassFootprint
     */
    protected function getClassFootprint(ClassScanner $class)
    {
        $footprint = new ClassFootprint();

        if ($class->isInterface()) {
            $footprint->setType(ClassFootprint::TYPE_INTERFACE);
        } elseif ($class->isTrait()) {
            $footprint->setType(ClassFootprint::TYPE_TRAIT);
        }

        $footprint->setParent($class->getParentClass());

        foreach ($class->getConstants(false) as $constant) {
            $footprint->addConstant($constant->getName(), $constant->getValue());
        }

        foreach ($class->getInterfaces() as $interface) {
            $footprint->addInterface($interface);
        }

        return $footprint;
    }

    /**
     * Get code scanner
     *
     * @return DirectoryCodeScanner
     */
    public function getScanner()
    {
        if (!$this->_scanner) {
            $this->_scanner = new DirectoryCodeScanner();
        }
        return $this->_scanner;
    }

    /**
     * Set code scanner
     *
     * @param DirectoryCodeScanner $scanner
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function setScanner(DirectoryCodeScanner $scanner)
    {
        $this->_scanner = $scanner;

        return $this;
    }
}
