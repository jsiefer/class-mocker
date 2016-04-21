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
 * Class ClassMockerTest
 */
class ClassMockerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        BaseMock::setDefaultCallBehavior(BaseMock::DEFAULT_BEHAVIOUR_RETURN_NULL);
    }

    /**
     * Test auto loading based on wildcards
     *
     * e.g.
     * mock('Namespace_*')
     *
     * @return void
     * @test
     */
    public function testWildcardAutoload()
    {
        $classMocker = new ClassMocker();
        $classMocker->mock('SomeClass');
        $classMocker->mock('Foobar_*');
        $classMocker->mock('Bar_Foo_*Collection');
        $classMocker->mock('Foo\Bar\*');

        $assertion = [
            'SomeClass'                     => true,
            'Foobar_HelloWorld'             => true,
            'Bar_Foo_Model_TestCollection'  => true,
            'Foo\Bar\TestClass'             => true,
            'Bar_Foo_Model_Sample'          => false,
            'SomeClass_Test'                => false,
            'Foo\Test\Bar'                  => false,
        ];

        foreach ($assertion as $className => $expect) {
            $result = $classMocker->autoload($className);
            $this->assertEquals(
                $expect,
                $result,
                $expect
                    ? "Should autoload class '$className' "
                    : "Should NOT autoload class '$className' "

            );
        }
    }

    /**
     * Test class initialization and make sure it
     * extends from the BaseMock class
     *
     * @return void
     * @test
     */
    public function testClassInitialization()
    {
        $classMocker = new ClassMocker();
        $classMocker->mock('Foobar\*');
        $classMocker->autoload('Foobar\Test\HelloWorld');

        $instance = new \Foobar\Test\HelloWorld();

        $this->assertInstanceOf(
            BaseMock::class,
            $instance,
            'All created mock instances should extent from BaseMock'
        );
    }

    /**
     * Optional autoload should only load classes that could
     * not get loaded by composer
     *
     * @return void
     * @test
     */
    public function testOptionalAutoload()
    {
        $defaultClass = 'Foobar\NotOptional\Foobar';
        $optionalClass = 'Foobar\Optional\Foobar';

        $classMocker = new ClassMocker();
        $classMocker->mock($defaultClass, false);
        $classMocker->mock($optionalClass, true);

        $this->assertFalse(
            $classMocker->autoload($optionalClass),
            'Class should not get loaded using the default autoload'
        );
        $this->assertFalse(
            $classMocker->autoloadOptional($defaultClass),
            'Class should not get loaded using the optional autoload'
        );
        $this->assertTrue(
            $classMocker->autoload($defaultClass),
            'Class should not get loaded using the default autoload'
        );
        $this->assertTrue(
            $classMocker->autoloadOptional($optionalClass),
            'Class should get loaded using the optional autoload'
        );
    }

    /**
     * When generation dir is defined all generated classes
     * should get saved and cached in the given directory
     *
     * @return void
     * @test
     */
    public function testGenerationDirFolder()
    {
        $vfs = vfsStream::setup('generation');

        $classMocker = new ClassMocker();
        $classMocker->setGenerationDir($vfs->url());
        $classMocker->mock('Foobar\*');
        $classMocker->autoload('Foobar\Test\GenerationDir');

        $this->assertTrue(
            $vfs->hasChild('Foobar/Test/GenerationDir.php'),
            'Should have created cache class file in generation dir'
        );
    }

    /**
     * Check and validate generation folder
     *
     * @return void
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Failed to create class generation folder
     */
    public function shouldFailOnInvalidGenerationDir()
    {
        $vfs = vfsStream::setup('generation');

        $testDir = $vfs->url() . '/test';

        file_put_contents($testDir, 'Not a dir!');

        $classMocker = new ClassMocker;
        $classMocker->setGenerationDir($testDir);
        $classMocker->mock('ShouldFailOnInvalidGenerationDirTestClass');
        $classMocker->autoload('ShouldFailOnInvalidGenerationDirTestClass');
    }

    /**
     * Should throw an exception if class has been loaded already
     *
     * @return void
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to generate and load already existing class
     */
    public function shouldThrowExceptionOnLoadingExistingClass()
    {
        $classMocker = new ClassMocker();
        $classMocker->mock(self::class);
        $classMocker->autoload(self::class);
    }

    /**
     * Trait should not allow any magic methods
     *
     * Magic methods like __call() can not be used by traits
     * without causing conflict with the BaseMock
     *
     * Instead use ___call() methods
     *
     * @return void
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Trait magic method ClassUsingInvalidTrait::__call()
     */
    public function shouldFailOnInvalidTraitMethod()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->registerTrait(InvalidTrait::class, 'ClassUsingInvalidTrait');
        $fwMocker->mock('ClassUsingInvalidTrait');
        $fwMocker->autoload('ClassUsingInvalidTrait');
    }

    /**
     * TestClass implements the Foo_Bar_Interface which should
     * get created automatically
     *
     * @return void
     * @test
     * @see \JSiefer\ClassMocker\TestClasses\TestClass
     */
    public function testInterfaceLoading()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->mock('Foo*');
        $fwMocker->enable();

        $test = new TestClass();
        $this->assertInstanceOf(
            'Foo_Bar_Interface',
            $test,
            'Unable to create interface on the fly'
        );

        $fwMocker->disable();
    }

    /**
     * If register base class they must implement the BaseMock class
     *
     * @return void
     * @test
     * @see \JSiefer\ClassMocker\TestClasses\TestMock
     */
    public function testBaseClass()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->mock('MyMock_*');
        $fwMocker->registerBaseClass(TestMock::class);
        $fwMocker->enable();

        $instance = new \MyMock_TestA();

        $this->assertInstanceOf(
            BaseMock::class,
            $instance,
            'Instance does not extend from BaseMock'
        );
        $this->assertInstanceOf(
            TestMock::class,
            $instance,
            'Instance does not extend from registered base class'
        );

        $fwMocker->disable();
    }

    /**
     * Test PHPUnit expects method
     *
     * @return void
     * @test
     */
    public function testExpectationMethods()
    {
        $fwMocker = new ClassMocker;
        $fwMocker->mock('Expect_*');
        $fwMocker->enable();

        $test = new \Expect_Something();
        $test->expects($this->once())
             ->method('hello')
             ->will($this->returnValue('Hello World'));

        $this->assertEquals('Hello World', $test->hello());

        $fwMocker->disable();
    }

    /**
     * Test footprints imported from json test file
     *
     * @return void
     * @test
     */
    public function testFootprintJsonImport()
    {
        $fwMocker = new ClassMocker();
        $fwMocker->importFootprints(__DIR__ . '/_data/test.ref.json');
        $fwMocker->mock('JSiefer\ClassMocker\TestFramework\*');
        $fwMocker->enable();

        $test = new ObjectB();

        $this->assertEquals(
            'foobar',
            ObjectA::EVENT,
            'Constant was not loaded correctly from test.ref.json'
        );
        $this->assertEquals(
            100,
            ObjectA::SORT,
            'Constant was not loaded correctly from test.ref.json'
        );

        $this->assertInstanceOf(ObjectA::class, $test);
        $this->assertInstanceOf(ObjectB::class, $test);
        $this->assertInstanceOf(InterfaceB::class, $test);
        $this->assertInstanceOf(BaseMock::class, $test);
    }

    /**
     * Test framework register
     *
     * @return void
     * @test
     */
    public function testFrameworkMock()
    {
        $framework = $this->getMockForAbstractClass(FrameworkInterface::class);
        $framework->expects($this->once())->method('register');

        $fwMocker = new ClassMocker();
        $fwMocker->mockFramework($framework);
    }
}
