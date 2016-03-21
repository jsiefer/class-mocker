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
 * Class ClassFootprintTest
 *
 * @covers \JSiefer\ClassMocker\Footprint\ClassFootprint
 */
class ClassFootprintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Simple Test
     *
     * @return array
     * @test
     */
    public function test()
    {
        $footprint = new ClassFootprint();
        $footprint->setType($footprint::TYPE_CLASS);
        $footprint->setParent('');
        $footprint->setParent('Test');

        $footprint->setConstants(['FOO' => 'foo']);
        $footprint->addConstant('A', '1');
        $footprint->addConstant('B', '1.5');
        $footprint->addConstant('C', 'test');

        $footprint->setInterfaces(['InterfaceA']);
        $footprint->addInterface('/InterfaceB');


        $this->assertEquals($footprint::TYPE_CLASS, $footprint->getType());
        $this->assertEquals('\Test', $footprint->getParent());

        $this->assertCount(4, $footprint->getConstants());
        $this->assertCount(2, $footprint->getInterfaces());
        $this->assertFalse($footprint->isInterface());


        $footprint->setType($footprint::TYPE_INTERFACE);
        $this->assertTrue($footprint->isInterface());

        $data = $footprint->export();

        return $data;
    }

    /**
     * @param array $data
     * @return void
     * @test
     * @depends test
     */
    public function testImport(array $data)
    {
        $footprint = new ClassFootprint($data);
        $this->assertEquals('\Test', $footprint->getParent());
        $this->assertCount(4, $footprint->getConstants());
        $this->assertCount(2, $footprint->getInterfaces());
        $this->assertTrue($footprint->isInterface());
    }

    /**
     * Should fail on invalid data
     *
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid footprint array
     */
    public function shouldFailOnInvalidData()
    {
        new ClassFootprint([0,0]);
    }

}
