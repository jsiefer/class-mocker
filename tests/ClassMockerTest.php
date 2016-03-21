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

use JSiefer\ClassMocker\Mock\BaseMock;
use JSiefer\ClassMocker\TestClasses\DummyTrait;
use JSiefer\ClassMocker\TestClasses\TestClass;
use JSiefer\ClassMocker\TestClasses\TestMock;
use JSiefer\ClassMocker\TestClasses\TraitA;
use JSiefer\ClassMocker\TestClasses\TraitB;
use JSiefer\ClassMocker\TestClasses\TraitC;
use JSiefer\ClassMocker\TestFramework\Data\ObjectA;
use org\bovigo\vfs\vfsStream;

/**
 * Class ClassMockerTest
 */
class ClassMockerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testAutoload()
    {
        $vfs = vfsStream::setup('generation');
        $fwMocker = new ClassMocker;
        $fwMocker->setGenerationDir($vfs->url('generation'));
        $fwMocker->mock('SomeClass');
        $fwMocker->mock('Foobar_*');
        $fwMocker->mock('Bar_Foo_*Collection');
        $fwMocker->mock('Testing\A\*Test');

        // check auto loads
        $this->assertTrue($fwMocker->autoload('SomeClass'));
        $this->assertTrue($fwMocker->autoload('Foobar_HelloWorld'));
        $this->assertTrue($fwMocker->autoload('Bar_Foo_Model_TestCollection'));
        $this->assertTrue($fwMocker->autoload('Testing\A\FoobarTest'));

        $this->assertFalse($fwMocker->autoload('Bar_Foo_Model_Sample'));
        $this->assertFalse($fwMocker->autoload('SomeClass_Test'));
        $this->assertFalse($fwMocker->autoload('Testing\A\Foo'));

        // check that class actually exist
        $instance = new \Foobar_HelloWorld();
        $this->assertInstanceOf(BaseMock::class, $instance);

        $this->assertTrue($vfs->hasChild('generation/SomeClass.php'));
        $this->assertTrue($vfs->hasChild('generation/Foobar_HelloWorld.php'));
        $this->assertTrue($vfs->hasChild('generation/Bar_Foo_Model_TestCollection.php'));
        $this->assertTrue($vfs->hasChild('generation/Testing/A/FoobarTest.php'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @depends testAutoload
     */
    public function testAutoLoadExistingClass()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->mock('SomeClass');

        $fwMocker->autoload('SomeClass');

    }

    /**
     * @test
     */
    public function testTraitInclusion()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->mock('Foobar*');
        $fwMocker->mock('Demo\*Collection');
        $fwMocker->registerTrait(TraitA::class);
        $fwMocker->registerTrait(TraitB::class);
        $fwMocker->registerTrait(TraitC::class);

        // test @for and @sort overwrite
        $fwMocker->registerTrait(TraitA::class, 'Demo\*Collection', 0);
        $fwMocker->registerTrait(TraitB::class, 'Demo\*Collection', 50);
        $fwMocker->registerTrait(TraitC::class, 'Demo\*Collection', 100);
        $fwMocker->registerTrait(DummyTrait::class, 'Demo\*Collection');
        $fwMocker->enable();

        $instance = new \Foobar_MyTrait();

        // check that all trait:__init methods are called.
        $this->assertEquals('HelloWorld!!!', $instance->output);

        // check common predefined trait usage
       // $instance->setFoo('bar');
        //$this->assertEquals('bar', $instance->getData('foo'));
        //$this->assertEquals('bar', $instance->getFoo());


        $this->assertInstanceOf('Foobar_MyTrait', $instance);
        $this->assertEquals('TraitC:talk', $instance->talk());
        $this->assertEquals('TraitC:listen', $instance->listen());
        $this->assertEquals('TraitC:jump', $instance->jump());
        $this->assertEquals('TraitB:show', $instance->show());
        $this->assertEquals('TraitB:hide', $instance->hide());
        $this->assertEquals('TraitA:read', $instance->read());

        // test different orders
        $collection = new \Demo\TestCollection();
        $this->assertEquals('TraitA:talk', $collection->talk());
        $this->assertEquals('TraitA:show', $collection->show());

        $fwMocker->disable();
    }

    /**
     * @test
     */
    public function testInterfaceLoading()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->mock('Foo*');
        $fwMocker->enable();

        $test = new TestClass();
        $this->assertInstanceOf('Foo_Bar_Interface', $test);

        $fwMocker->disable();
    }


    public function testBaseClass()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->mock('MyMock_*');
        $fwMocker->registerBaseClass(TestMock::class);
        $fwMocker->enable();

        $instance = new \MyMock_TestA();

        $this->assertInstanceOf(BaseMock::class, $instance);
        $this->assertInstanceOf(TestMock::class, $instance);

        $fwMocker->disable();
    }


    public function testExpectationMethods()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->mock('Expect_*');
        $fwMocker->enable();

        $test = new \Expect_Something();
        $test->expects($this->once())->method('hello')->willReturn('Hello World');
        $this->assertEquals('Hello World', $test->hello());

        $fwMocker->disable();
    }


    public function testFootprintJsonImport()
    {
        $fwMocker = new ClassMocker();
        $fwMocker->importFootprints(__DIR__ . '/_data/test.ref.json');
        $fwMocker->mock('JSiefer\ClassMocker\TestFramework\*');
        $fwMocker->enable();

        $test = new ObjectA();

        $this->assertEquals('foobar', ObjectA::EVENT);
        $this->assertEquals(100, ObjectA::SORT);
        $this->assertInstanceOf(BaseMock::class, $test);
    }

}
