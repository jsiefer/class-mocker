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

use JSiefer\ClassMocker\TestClasses\TraitA;
use JSiefer\ClassMocker\TestClasses\TraitB;
use JSiefer\ClassMocker\TestClasses\TraitC;
use JSiefer\ClassMocker\TestClasses\TraitSample;


/**
 * Class TraitRegistryTest
 *
 * @covers \JSiefer\ClassMocker\Registry\TraitRegistry
 */
class TraitRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test sorting of traits
     *
     * @return void
     * @test
     */
    public function testSorting()
    {
        $registry = new TraitRegistry();
        $registry->register(TraitB::class);
        $registry->register(TraitC::class);
        $registry->register(TraitSample::class, 'Foobar_*', 0);
        $registry->register(TraitA::class);

        $result = $registry->findByClass('Foobar_MyTrait');

        $this->assertEquals(TraitA::class, $result[0]->getName());
        $this->assertEquals(TraitB::class, $result[1]->getName());
        $this->assertEquals(TraitC::class, $result[2]->getName());
        $this->assertEquals(TraitSample::class, $result[3]->getName());
    }

    /**
     * A pattern for trait is required
     *
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No `pattern` defined for trait
     */
    public function shouldFailOnMissingPattern()
    {
        $registry = new TraitRegistry();
        $registry->register(TraitSample::class);
    }

}
