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

use JSiefer\ClassMocker\Footprint\ClassFootprint;

/**
 * Class FootprintRegistry
 */
class FootprintRegistry
{
    /**
     * Raw footprint data from json files
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Registered class footprints
     *
     * @var array[]
     */
    protected $_footprints = [];

    /**
     * Matching name patter that will be used to create interfaces
     *
     * @var string
     */
    protected $_interfaceRegex = '/Interface$/i';

    /**
     * Import footprints from json file
     *
     * @param string $file
     *
     * @return void
     */
    public function import($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("Footprint file '$file' does not exist");
        }
        $content = file_get_contents($file);
        $this->importJson($content);
    }

    /**
     * Import footprints from json string
     *
     * @param string $jsonData
     *
     * @return void
     */
    public function importJson($jsonData)
    {
        $json = json_decode($jsonData, true);

        if ($json === null) {
            throw new \InvalidArgumentException("Footprint json is not a valid");
        }

        if (!is_array($json)) {
            throw new \InvalidArgumentException("Footprint json data is not a valid");
        }

        $this->_data += $json;
    }


    /**
     * Retrieve class footprint
     *
     * @param string $className
     * @return ClassFootprint
     */
    public function get($className)
    {
        if (isset($this->_footprints[$className])) {
            return $this->_footprints[$className];
        }

        if (isset($this->_data[$className])) {
            $data = $this->_data[$className];
            $footprint = new ClassFootprint($data);
        } else {
            $footprint = new ClassFootprint();
            if (preg_match($this->_interfaceRegex, $className)) {
                $footprint->setType(ClassFootprint::TYPE_INTERFACE);
            }
        }

        $this->_footprints[$className] = $footprint;

        return $footprint;
    }

}
