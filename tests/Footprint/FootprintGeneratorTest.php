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
 * Class ScannerTest
 *
 * @covers \JSiefer\ClassMocker\Footprint\FootprintGenerator
 */
class FootprintGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @test
     * @return void
     */
    public function testScan()
    {
        $testDir = dirname(__DIR__) . '/_data/test_framework';

        $generator = new FootprintGenerator($testDir);

        $footprintJson = $generator->generate();

        $this->assertJson($footprintJson);
        $json = json_decode($footprintJson, true);

        $this->assertArrayHasKey('JSiefer\ClassMocker\TestFramework\BaseClass', $json);
        $this->assertArrayHasKey('JSiefer\ClassMocker\TestFramework\Data\ObjectA', $json);
        $this->assertArrayHasKey('JSiefer\ClassMocker\TestFramework\InterfaceA', $json);

        $this->assertEquals(
            [ClassFootprint::TYPE_INTERFACE, ['PATH' => 'somewhere'], [], null],
            $json['JSiefer\ClassMocker\TestFramework\InterfaceA']
        );

        $this->assertEquals(
            [
                ClassFootprint::TYPE_CLASS,
                ['TEST' => 'test', 'TWO' => '2'],
                ['\JSiefer\ClassMocker\TestFramework\InterfaceA'],
                '\JSiefer\ClassMocker\TestFramework\BaseClass'
            ],
            $json['JSiefer\ClassMocker\TestFramework\ClassA']
        );

        //file_put_contents(dirname(__DIR__) . '/_data/test.ref.json', $footprintJson);
    }
}
