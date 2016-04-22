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


/**
 * Class ClassFootprint
 *
 * @see \JSiefer\ClassMocker\Footprint\ClassFootprintTest
 */
class ClassFootprint
{
    const TYPE_CLASS = 1;
    const TYPE_INTERFACE = 2;
    const TYPE_TRAIT = 3;

    /**
     * Type (TYPE_CLASS|TYPE_INTERFACE|TYPE_TRAIT)
     *
     * @var integer
     */
    protected $_type = self::TYPE_CLASS;

    /**
     * Constants defined by class
     *
     * @var string[]
     */
    protected $_constants = [];

    /**
     * Interfaces used by class
     *
     * @var string[]
     */
    protected $_interfaces = [];

    /**
     * Parent class that this class extends from
     *
     * @var string
     */
    protected $_parent = null;

    /**
     * ClassFootprint constructor.
     *
     * @param array $data
     */
    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->import($data);
        }
    }

    /**
     * Import from array
     *
     * @param array $data
     * @return $this
     */
    public function import(array $data)
    {
        if (count($data) !== 4) {
            throw new \InvalidArgumentException("Invalid footprint array");
        }

        $this->_type = $data[0];
        $this->_constants = $data[1];
        $this->_interfaces = $data[2];
        $this->_parent = $data[3];

        return $this;
    }

    /**
     * Export as array
     *
     * @return array
     */
    public function export()
    {
        return [
            $this->_type,
            $this->_constants,
            $this->_interfaces,
            $this->_parent
        ];
    }

    /**
     * Check if footprint is an interface
     *
     * @return bool
     */
    public function isInterface()
    {
        return $this->getType() === self::TYPE_INTERFACE;
    }

    /**
     * Retrieve Constants
     *
     * @return \string[]
     */
    public function getConstants()
    {
        return $this->_constants;
    }

    /**
     * Set Constants
     *
     * @param \string[] $constants
     *
     * @return $this
     */
    public function setConstants(array $constants)
    {
        $this->_constants = [];
        foreach ($constants as $name => $value) {
            $this->addConstant($name, $value);
        }

        return $this;
    }

    /**
     * Add constant
     *
     * @param string $name
     * @param string|float $value
     *
     * @return $this
     */
    public function addConstant($name, $value)
    {
        if (is_numeric($value)) {
            $value = (float)$value;
        }
        $this->_constants[$name] = $value;
        return $this;
    }

    /**
     * Retrieve Interfaces
     *
     * @return \string[]
     */
    public function getInterfaces()
    {
        return $this->_interfaces;
    }

    /**
     * Set Interfaces
     *
     * @param \string[] $interfaces
     *
     * @return $this
     */
    public function setInterfaces(array $interfaces)
    {
        $this->_interfaces = [];
        foreach ($interfaces as $interface) {
            $this->addInterface($interface);
        }
        return $this;
    }

    /**
     * Add interface
     *
     * @param string $interface
     *
     * @return $this
     */
    public function addInterface($interface)
    {
        if (!empty($interface)) {
            $this->_interfaces[] = $this->_normalizeClass($interface);
        }
        return $this;
    }

    /**
     * Retrieve Parent
     *
     * @return string
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Set Parent
     *
     * @param string|null $parent
     *
     * @return $this
     */
    public function setParent($parent)
    {
        if (!empty($parent)) {
            $this->_parent = $this->_normalizeClass($parent);
        } else {
            $this->_parent = null;
        }

        return $this;
    }

    /**
     * Retrieve Type
     *
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set Type
     *
     * @param int $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    protected function _normalizeClass($className)
    {
        return '\\' . trim($className, '\\');
    }
}
