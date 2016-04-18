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

/**
 * Class nextTest
 * @covers \JSiefer\ClassMocker\next
 */
class nextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The method next::parent() can only be called within
     * trait-methods that mock a original method
     *
     * @test
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage next:parent() call is only allowed in trait calls
     */
    public function shouldThrowException()
    {
        next::parent();
    }
}
