<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Entity;

use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @package JsonSchema\Tests\Entity
 *
 * @author Joost Nijhuis <jnijhuis81@gmail.com>
 */
class JsonPointerTest extends TestCase
{
    /**
     * @dataProvider jsonPointerDataProvider
     */
    public function testJsonPointer(
        string $testValue,
        string $expectedFileName,
        array $expectedPropertyPaths,
        string $expectedPropertyPathAsString,
        string $expectedToString
    ): void {
        $jsonPointer = new JsonPointer($testValue);
        $this->assertEquals($expectedFileName, $jsonPointer->getFilename());
        $this->assertEquals($expectedPropertyPaths, $jsonPointer->getPropertyPaths());
        $this->assertEquals($expectedPropertyPathAsString, $jsonPointer->getPropertyPathAsString());
        $this->assertEquals($expectedToString, (string) $jsonPointer);
    }

    public static function jsonPointerDataProvider(): \Generator
    {
        yield 'testDataSet_01' => [
            'testValue'                    => '#/definitions/date',
            'expectedFileName'             => '',
            'expectedPropertyPaths'        => ['definitions', 'date'],
            'expectedPropertyPathAsString' => '#/definitions/date',
            'expectedToString'             => '#/definitions/date'
        ];
        yield 'testDataSet_02' => [
            'testValue'                    => 'https://www.example.com/definitions.json#/definitions/date',
            'expectedFileName'             => 'https://www.example.com/definitions.json',
            'expectedPropertyPaths'        => ['definitions', 'date'],
            'expectedPropertyPathAsString' => '#/definitions/date',
            'expectedToString'             => 'https://www.example.com/definitions.json#/definitions/date'
        ];
        yield 'testDataSet_03' => [
            'testValue'                    => '/tmp/schema.json#definitions/common/date/',
            'expectedFileName'             => '/tmp/schema.json',
            'expectedPropertyPaths'        => ['definitions', 'common', 'date'],
            'expectedPropertyPathAsString' => '#/definitions/common/date',
            'expectedToString'             => '/tmp/schema.json#/definitions/common/date'
        ];
        yield 'testDataSet_04' => [
            'testValue'                    => './definitions.json#',
            'expectedFileName'             => './definitions.json',
            'expectedPropertyPaths'        => [],
            'expectedPropertyPathAsString' => '#',
            'expectedToString'             => './definitions.json#'
        ];
        yield 'testDataSet_05' => [
            'testValue'                    => '/schema.json#~0definitions~1general/%custom%25',
            'expectedFileName'             => '/schema.json',
            'expectedPropertyPaths'        => ['~definitions/general', '%custom%'],
            'expectedPropertyPathAsString' => '#/~0definitions~1general/%25custom%25',
            'expectedToString'             => '/schema.json#/~0definitions~1general/%25custom%25'
        ];
        yield 'testDataSet_06' => [
            'testValue'                    => '#/items/0',
            'expectedFileName'             => '',
            'expectedPropertyPaths'        => ['items', '0'],
            'expectedPropertyPathAsString' => '#/items/0',
            'expectedToString'             => '#/items/0'
        ];
    }

    public function testJsonPointerWithPropertyPaths(): void
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

    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ref value must be a string');

        new JsonPointer(null);
    }
}
