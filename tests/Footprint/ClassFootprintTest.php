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
     * Test type methods
     *
     * @return void
     * @test
     */
    public function testType()
    {
        $footprint = new ClassFootprint();

        $this->assertEquals(
            ClassFootprint::TYPE_CLASS,
            $footprint->getType(),
            'Class should be the default type'
        );

        $this->assertFalse(
            $footprint->isInterface(),
            'Should not be an interface by default'
        );

        $footprint->setType(ClassFootprint::TYPE_INTERFACE);
        $this->assertTrue(
            $footprint->isInterface(),
            'Should be an interface'
        );
    }

    /**
     * Test Parent Setter and Getter
     *
     * @return void
     * @test
     */
    public function testParentSetter()
    {
        $footprint = new ClassFootprint();

        $footprint->setParent('');
        $this->assertNull(
            $footprint->getParent(),
            'Empty parent class must result in NULL'
        );

        $footprint->setParent('Test');
        $this->assertEquals(
            '\Test',
            $footprint->getParent(),
            'Add root back-slash if missing'
        );

        $footprint->setParent('\Test');
        $this->assertEquals(
            '\Test',
            $footprint->getParent(),
            'Only add root back-slash if missing'
        );
    }

    /**
     * Test interface setter methods
     *
     * @return void
     * @test
     */
    public function testInterfaceSetter()
    {
        $footprint = new ClassFootprint();

        $footprint->setInterfaces(['InterfaceA', '\InterfaceB']);
        $this->assertEquals(
            ['\InterfaceA', '\InterfaceB'],
            $footprint->getInterfaces(),
            'Add root back-slash if missing'
        );

        $footprint->setInterfaces(['Foobar']);
        $this->assertEquals(
            ['\Foobar'],
            $footprint->getInterfaces(),
            'Set must overwrite existing interfaces'
        );

        $footprint->addInterface('Barfoo');
        $this->assertEquals(
            ['\Foobar', '\Barfoo'],
            $footprint->getInterfaces(),
            'Add must NOT overwrite existing interfaces'
        );

        $footprint->addInterface('');
        $this->assertEquals(
            ['\Foobar', '\Barfoo'],
            $footprint->getInterfaces(),
            'Add must ignore empty interfaces'
        );
    }

    /**
     * Test constant setter methods
     *
     * @return void
     * @test
     */
    public function testConstantSetter()
    {
        $footprint = new ClassFootprint();

        $footprint->setConstants(['FOO' => 'foo', 'TWO' => '2']);
        $this->assertSame(
            ['FOO' => 'foo', 'TWO' => 2.0],
            $footprint->getConstants(),
            'Strings must be checked vor number values'
        );

        $footprint->setConstants(['FOO' => 'foo']);
        $this->assertEquals(
            ['FOO' => 'foo'],
            $footprint->getConstants(),
            'Set must replace existing constants'
        );

        $footprint->addConstant('BAR', 'bar');
        $this->assertEquals(
            ['FOO' => 'foo', 'BAR' => 'bar'],
            $footprint->getConstants(),
            'Add must NOT replace existing constants'
        );
    }

    /**
     * Simple export and import test
     *
     * Footprint can be exported to simple arrays and then
     * re-imported
     *
     * The exported footprint data should be as small as possible
     * to later easily create small json reference files
     *
     * @return void
     * @test
     */
    public function testExportAndImport()
    {
        $source = new ClassFootprint();
        $source->setType(ClassFootprint::TYPE_INTERFACE);
        $source->setParent('Test');
        $source->addConstant('A', '1');
        $source->addConstant('B', '1.5');
        $source->addConstant('C', 'test');
        $source->setInterfaces(['InterfaceA', 'InterfaceB']);

        $data = $source->export();
        $this->assertInternalType(
            'array',
            $data,
            'Exported data should be an array'
        );

        $target = new ClassFootprint($data);

        $this->assertSame(
            $target->getType(),
            $source->getType(),
            'Should have copied type'
        );

        $this->assertSame(
            $target->getParent(),
            $source->getParent(),
            'Should have copied parent class'
        );

        $this->assertSame(
            $target->getConstants(),
            $source->getConstants(),
            'Should have copied constants'
        );

        $this->assertSame(
            $target->getInterfaces(),
            $source->getInterfaces(),
            'Should have copied interface'
        );
    }

    /**
     * Should fail on invalid data
     *
     * The footprint data must be an array of size 4
     *
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid footprint array
     */
    public function shouldFailOnInvalidData()
    {
        new ClassFootprint([0,0]);
    }
}
