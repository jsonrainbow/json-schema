<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Tool;

use JsonSchema\Tool\DeepComparer;
use PHPUnit\Framework\TestCase;

class DeepComparerTest extends TestCase
{
    /**
     * @dataProvider equalDataProvider
     */
    public function testComparesDeepEqualForEqualLeftAndRight($left, $right): void
    {
        self::assertTrue(DeepComparer::isEqual($left, $right));
    }

    /**
     * @dataProvider notEqualDataProvider
     */
    public function testComparesDeepEqualForNotEqualLeftAndRight($left, $right): void
    {
        self::assertFalse(DeepComparer::isEqual($left, $right));
    }

    public function equalDataProvider(): \Generator
    {
        yield 'Boolean true' => [true, true];
        yield 'Boolean false' => [false, false];

        yield 'Integer one' => [1, 1];
        yield 'Integer INT MIN' => [PHP_INT_MIN, PHP_INT_MIN];
        yield 'Integer INT MAX' => [PHP_INT_MAX, PHP_INT_MAX];

        yield 'Float PI' => [M_PI, M_PI];

        yield 'String' => ['hello world!', 'hello world!'];

        yield 'array of integer' => [[1, 2, 3], [1, 2, 3]];
        yield 'object of integer' => [(object) [1, 2, 3], (object) [1, 2, 3]];

        yield 'nested objects of integers' => [
            (object) [1 => (object) range(1, 10), 2 => (object) range(50, 60)],
            (object) [1 => (object) range(1, 10), 2 => (object) range(50, 60)],
        ];
    }

    public function notEqualDataProvider(): \Generator
    {
        yield 'Boolean true/false' => [true, false];

        yield 'Integer one/two' => [1, 2];
        yield 'Integer INT MIN/MAX' => [PHP_INT_MIN, PHP_INT_MAX];

        yield 'Float PI/' => [M_PI, M_E];

        yield 'String' => ['hello world!', 'hell0 w0rld!'];

        yield 'array of integer with smaller left side' => [[1, 3], [1, 2, 3]];
        yield 'array of integer with smaller right side' => [[1, 2, 3], [1, 3]];
        yield 'object of integer with smaller left side' => [(object) [1, 3], (object) [1, 2, 3]];
        yield 'object of integer with smaller right side' => [(object) [1, 2, 3], (object) [1, 3]];

        yield 'nested objects of integers with different left hand side' => [
            (object) [1 => (object) range(1, 10), 2 => (object) range(50, 60, 2)],
            (object) [1 => (object) range(1, 10), 2 => (object) range(50, 60)],
        ];
        yield 'nested objects of integers with different right hand side' => [
            (object) [1 => (object) range(1, 10), 2 => (object) range(50, 60)],
            (object) [1 => (object) range(1, 10), 2 => (object) range(50, 60, 2)],
        ];

        $options = [
            'boolean' => true,
            'integer' => 42,
            'float' => M_PI,
            'string' => 'hello world!',
            'array' => [1, 2, 3],
            'object' => (object) [1, 2, 3],
        ];

        foreach ($options as $leftType => $leftValue) {
            foreach ($options as $rightType => $rightValue) {
                if ($leftType === $rightType) {
                    continue;
                }

                yield sprintf('%s vs. %s', $leftType, $rightType) => [$leftValue, $rightValue];
            }
        }
    }

}
