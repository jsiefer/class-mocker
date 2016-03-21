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


/**
 * Class FootprintRegistryTest
 *
 * @covers \JSiefer\ClassMocker\Registry\FootprintRegistry
 */
class FootprintRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test import from json file
     *
     * @test
     */
    public function testImport()
    {
        $registry = new FootprintRegistry();
        $registry->import(dirname(__DIR__) . '/_data/test.ref.json');

        $footprint = $registry->get('JSiefer\ClassMocker\TestFramework\BaseClass');

        $this->assertFalse($footprint->isInterface());
        $this->assertNull($footprint->getParent());
        $constants = $footprint->getConstants();
        $this->assertCount(2, $constants);
        $this->assertSame('test', $constants['TEST']);
        $this->assertSame(1, $constants['ONE']);

        $cached = $registry->get('JSiefer\ClassMocker\TestFramework\BaseClass');

        $this->assertSame($footprint, $cached);
    }

    /**
     * Should create interface prints and class prints
     *
     * @test
     */
    public function testInterface()
    {
        $registry = new FootprintRegistry();

        $footprint = $registry->get('FooBar');
        $this->assertFalse($footprint->isInterface());

        $footprint = $registry->get('FooBarInterface');
        $this->assertTrue($footprint->isInterface());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Footprint file 'not-existing-file.json' does not exist
     */
    public function shouldFailOnInvalidFile()
    {
        $registry = new FootprintRegistry();
        $registry->import('not-existing-file.json');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Footprint json is not a valid
     */
    public function shouldFailOnInvalidJson()
    {
        $registry = new FootprintRegistry();
        $registry->importJson('Some Invalid "JSON" Data');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Footprint json data is not a valid
     */
    public function shouldFailOnInvalidJsonData()
    {
        $registry = new FootprintRegistry();
        $registry->importJson('"test"');
    }
}
