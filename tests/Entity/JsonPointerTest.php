<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Entity;

use JsonSchema\Entity\JsonPointer;
use PHPUnit\Framework\TestCase;

/**
 * @package JsonSchema\Tests\Entity
 *
 * @author Joost Nijhuis <jnijhuis81@gmail.com>
 */
class JsonPointerTest extends TestCase
{
    /**
     * @dataProvider getTestData
     *
     * @param string $testValue
     * @param string $expectedFileName
     * @param array  $expectedPropertyPaths
     * @param string $expectedPropertyPathAsString
     * @param string $expectedToString
     */
    public function testJsonPointer(
        $testValue,
        $expectedFileName,
        $expectedPropertyPaths,
        $expectedPropertyPathAsString,
        $expectedToString
    ) {
        $jsonPointer = new JsonPointer($testValue);
        $this->assertEquals($expectedFileName, $jsonPointer->getFilename());
        $this->assertEquals($expectedPropertyPaths, $jsonPointer->getPropertyPaths());
        $this->assertEquals($expectedPropertyPathAsString, $jsonPointer->getPropertyPathAsString());
        $this->assertEquals($expectedToString, (string) $jsonPointer);
    }

    /**
     * @return array[]
     */
    public function getTestData()
    {
        return [
            'testDataSet_01' => [
                'testValue'                    => '#/definitions/date',
                'expectedFileName'             => '',
                'expectedPropertyPaths'        => ['definitions', 'date'],
                'expectedPropertyPathAsString' => '#/definitions/date',
                'expectedToString'             => '#/definitions/date'
            ],
            'testDataSet_02' => [
                'testValue'                    => 'http://www.example.com/definitions.json#/definitions/date',
                'expectedFileName'             => 'http://www.example.com/definitions.json',
                'expectedPropertyPaths'        => ['definitions', 'date'],
                'expectedPropertyPathAsString' => '#/definitions/date',
                'expectedToString'             => 'http://www.example.com/definitions.json#/definitions/date'
            ],
            'testDataSet_03' => [
                'testValue'                    => '/tmp/schema.json#definitions/common/date/',
                'expectedFileName'             => '/tmp/schema.json',
                'expectedPropertyPaths'        => ['definitions', 'common', 'date'],
                'expectedPropertyPathAsString' => '#/definitions/common/date',
                'expectedToString'             => '/tmp/schema.json#/definitions/common/date'
            ],
            'testDataSet_04' => [
                'testValue'                    => './definitions.json#',
                'expectedFileName'             => './definitions.json',
                'expectedPropertyPaths'        => [],
                'expectedPropertyPathAsString' => '#',
                'expectedToString'             => './definitions.json#'
            ],
            'testDataSet_05' => [
                'testValue'                    => '/schema.json#~0definitions~1general/%custom%25',
                'expectedFileName'             => '/schema.json',
                'expectedPropertyPaths'        => ['~definitions/general', '%custom%'],
                'expectedPropertyPathAsString' => '#/~0definitions~1general/%25custom%25',
                'expectedToString'             => '/schema.json#/~0definitions~1general/%25custom%25'
            ],
            'testDataSet_06' => [
                'testValue'                    => '#/items/0',
                'expectedFileName'             => '',
                'expectedPropertyPaths'        => ['items', '0'],
                'expectedPropertyPathAsString' => '#/items/0',
                'expectedToString'             => '#/items/0'
            ]
        ];
    }

    public function testJsonPointerWithPropertyPaths()
    {
        $initial = new JsonPointer('#/definitions/date');

        $this->assertEquals(['definitions', 'date'], $initial->getPropertyPaths());
        $this->assertEquals('#/definitions/date', $initial->getPropertyPathAsString());

        $modified = $initial->withPropertyPaths(['~definitions/general', '%custom%']);

        $this->assertNotSame($initial, $modified);

        $this->assertEquals(['definitions', 'date'], $initial->getPropertyPaths());
        $this->assertEquals('#/definitions/date', $initial->getPropertyPathAsString());

        $this->assertEquals(['~definitions/general', '%custom%'], $modified->getPropertyPaths());
        $this->assertEquals('#/~0definitions~1general/%25custom%25', $modified->getPropertyPathAsString());
    }

    public function testCreateWithInvalidValue()
    {
        $this->expectException('\JsonSchema\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Ref value must be a string');

        new JsonPointer(null);
    }
}
