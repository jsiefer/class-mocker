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
use JSiefer\ClassMocker\Mock\BaseMock;
use JSiefer\ClassMocker\TestClasses\DummyTrait;
use JSiefer\ClassMocker\TestClasses\InvalidTrait;
use JSiefer\ClassMocker\TestClasses\Readable;
use JSiefer\ClassMocker\TestClasses\Talkable;
use JSiefer\ClassMocker\TestClasses\TestClass;
use JSiefer\ClassMocker\TestClasses\TestMock;
use JSiefer\ClassMocker\TestClasses\TraitA;
use JSiefer\ClassMocker\TestClasses\TraitB;
use JSiefer\ClassMocker\TestClasses\TraitC;
use JSiefer\ClassMocker\TestFramework\Data\ObjectA;
use JSiefer\ClassMocker\TestFramework\Data\ObjectB;
use JSiefer\ClassMocker\TestFramework\InterfaceA;
use JSiefer\ClassMocker\TestFramework\InterfaceB;
use org\bovigo\vfs\vfsStream;

/**
 * Class TraitExtensionTest
 */
class TraitExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMocker
     */
    protected $classMocker;

    /**
     * Set up class mocker
     *
     * @return void
     */
    public function setUp()
    {
        BaseMock::setDefaultCallBehavior(BaseMock::DEFAULT_BEHAVIOUR_RETURN_NULL);

        $this->classMocker = new ClassMocker;
        //$classMocker->setGenerationDir('./var/generation');

        $this->classMocker->mock('Foobar\*');
        $this->classMocker->registerTrait(TraitA::class);
        $this->classMocker->registerTrait(TraitB::class);
        $this->classMocker->registerTrait(TraitC::class);
        $this->classMocker->registerTrait(DummyTrait::class, 'Foobar\*', 1000);

        // test footprints
        $footprintA = new ClassFootprint();
        $footprintA->addInterface(Readable::class);
        $footprintA->addInterface(Talkable::class);

        $footprintB = new ClassFootprint();
        $footprintB->setParent('Foobar\BaseClass');

        $this->classMocker->registerFootprint('Foobar\BaseClass', $footprintA);
        $this->classMocker->registerFootprint('Foobar\TestClass', $footprintB);
        $this->classMocker->enable();
    }

    /**
     * Tear down class mocker
     *
     * @return void
     */
    public function tearDown()
    {
        $this->classMocker->disable();
        $this->classMocker = null;
    }

    /**
     * Test that footprints work as expected
     *
     * @test
     * @see \JSiefer\ClassMocker\TraitExtensionTest::setUp()
     */
    public function testInstanceDefinedByFootprints()
    {
        $instance = new \Foobar\TestClass();
        $this->assertInstanceOf(
            'Foobar\BaseClass',
            $instance,
            'Instance should be a `Foobar\BaseClass` as defined by $footprintB'
        );
        $this->assertInstanceOf(
            Readable::class,
            $instance,
            'Instance should be a `Readable` as defined by $footprintA'
        );
        $this->assertInstanceOf(
            Talkable::class,
            $instance,
            'Instance should be a `Talkable` as defined by $footprintA'
        );
    }

    /**
     * Protected methods can only be called from the class scope
     *
     * @return void
     * @test
     * @see \JSiefer\ClassMocker\TestClasses\DummyTrait::protectedMethod()
     * @see \JSiefer\ClassMocker\TestClasses\DummyTrait::publicMethod()
     */
    public function testTraitMethodAccessibility()
    {
        $instance = new \Foobar\TestClass();

        $this->assertNull(
            $instance->protectedMethod(10),
            'Should not allow protected methods to be called from outside'
        );
        $this->assertEquals(
            20,
            $instance->publicMethod(10),
            'Unable to call protected methods through public method'
        );
    }

    /**
     * Test trait init methods
     *
     * @return void
     * @test
     * @see \JSiefer\ClassMocker\TestClasses\TraitA::___init()
     * @see \JSiefer\ClassMocker\TestClasses\TraitB::___init()
     * @see \JSiefer\ClassMocker\TestClasses\TraitC::___init()
     */
    public function testTraitInitMethod()
    {
        $instance = new \Foobar\TestClass();

        $this->assertEquals(
            'Hello World!!!',
            $instance->output,
            'Not all Traits::___init() methods have been called'
        );
    }

    /**
     * Traits can use ___call/___get() to mimic magic php methods
     *
     * @return void
     * @test
     * @see \JSiefer\ClassMocker\TestClasses\TraitA::___call()
     * @see \JSiefer\ClassMocker\TestClasses\TraitA::___get()
     */
    public function testMagicTraitMethods()
    {
        $instance = new \Foobar\TestClass();

        $this->assertTrue(
            $instance->getFoobar(),
            'Did not call TraitA::___call() method'
        );

        $this->assertEquals(
            'test',
            $instance->foobar,
            'Did not call TraitA::___get() method'
        );
    }

    /**
     * Test that all methods are called in the correct order
     * defined by the sortOrder parameter/tag
     *
     * @return void
     * @test
     */
    public function testTraitMethodSortOrder()
    {
        $msg = 'Sort order of methods are wrong';

        $instance = new \Foobar\TestClass();
        $this->assertEquals('TraitC:talk', $instance->talk('test'), $msg);
        $this->assertEquals('TraitC:listen', $instance->listen(), $msg);
        $this->assertEquals('TraitC:jump', $instance->jump(), $msg);
        $this->assertEquals('TraitB:show', $instance->show(), $msg);
        $this->assertEquals('TraitB:hide', $instance->hide(), $msg);
        $this->assertEquals('TraitA:read', $instance->read(), $msg);

        // force overwrite different order
        $this->classMocker->mock('Demo\*Collection');
        $this->classMocker->registerTrait(TraitA::class, 'Demo\*Collection', 0);
        $this->classMocker->registerTrait(TraitB::class, 'Demo\*Collection', 50);
        $this->classMocker->registerTrait(TraitC::class, 'Demo\*Collection', 100);
        $this->classMocker->registerTrait(DummyTrait::class, 'Demo\*Collection');

        // test different orders
        $collection = new \Demo\TestCollection();
        $this->assertEquals('TraitA:talk', $collection->talk('test'));
        $this->assertEquals('TraitA:show', $collection->show());
    }

    /**
     * Make sure method-stubs will always overwrite any
     * trait implementation
     *
     * @return void
     * @test
     */
    public function testTraitMethodStubs()
    {
        $instance = new \Foobar\TestClass();
        $instance->method('jump')->willReturn('I JUMPED');

        $this->assertEquals('I JUMPED', $instance->jump());
    }
}
