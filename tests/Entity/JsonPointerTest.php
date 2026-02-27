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
        return array(
            'testDataSet_01' => array(
                'testValue'                    => '#/definitions/date',
                'expectedFileName'             => '',
                'expectedPropertyPaths'        => array('definitions', 'date'),
                'expectedPropertyPathAsString' => '#/definitions/date',
                'expectedToString'             => '#/definitions/date'
            ),
            'testDataSet_02' => array(
                'testValue'                    => 'http://www.example.com/definitions.json#/definitions/date',
                'expectedFileName'             => 'http://www.example.com/definitions.json',
                'expectedPropertyPaths'        => array('definitions', 'date'),
                'expectedPropertyPathAsString' => '#/definitions/date',
                'expectedToString'             => 'http://www.example.com/definitions.json#/definitions/date'
            ),
            'testDataSet_03' => array(
                'testValue'                    => '/tmp/schema.json#definitions/common/date/',
                'expectedFileName'             => '/tmp/schema.json',
                'expectedPropertyPaths'        => array('definitions', 'common', 'date'),
                'expectedPropertyPathAsString' => '#/definitions/common/date',
                'expectedToString'             => '/tmp/schema.json#/definitions/common/date'
            ),
            'testDataSet_04' => array(
                'testValue'                    => './definitions.json#',
                'expectedFileName'             => './definitions.json',
                'expectedPropertyPaths'        => array(),
                'expectedPropertyPathAsString' => '#',
                'expectedToString'             => './definitions.json#'
            ),
            'testDataSet_05' => array(
                'testValue'                    => '/schema.json#~0definitions~1general/%custom%25',
                'expectedFileName'             => '/schema.json',
                'expectedPropertyPaths'        => array('~definitions/general', '%custom%'),
                'expectedPropertyPathAsString' => '#/~0definitions~1general/%25custom%25',
                'expectedToString'             => '/schema.json#/~0definitions~1general/%25custom%25'
            ),
            'testDataSet_06' => array(
                'testValue'                    => '#/items/0',
                'expectedFileName'             => '',
                'expectedPropertyPaths'        => array('items', '0'),
                'expectedPropertyPathAsString' => '#/items/0',
                'expectedToString'             => '#/items/0'
            )
        );
    }

    public function testJsonPointerWithPropertyPaths()
    {
        $initial = new JsonPointer('#/definitions/date');

        $this->assertEquals(array('definitions', 'date'), $initial->getPropertyPaths());
        $this->assertEquals('#/definitions/date', $initial->getPropertyPathAsString());

        $modified = $initial->withPropertyPaths(array('~definitions/general', '%custom%'));

        $this->assertNotSame($initial, $modified);

        $this->assertEquals(array('definitions', 'date'), $initial->getPropertyPaths());
        $this->assertEquals('#/definitions/date', $initial->getPropertyPathAsString());

        $this->assertEquals(array('~definitions/general', '%custom%'), $modified->getPropertyPaths());
        $this->assertEquals('#/~0definitions~1general/%25custom%25', $modified->getPropertyPathAsString());
    }

    public function testCreateWithInvalidValue()
    {
        $this->setExpectedException(
            '\JsonSchema\Exception\InvalidArgumentException',
            'Ref value must be a string'
        );
        new JsonPointer(null);
    }
}
