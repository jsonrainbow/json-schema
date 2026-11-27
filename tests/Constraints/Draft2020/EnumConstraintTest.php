<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft2020;

use JsonSchema\Constraints\Drafts\Draft2020\EnumConstraint;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;

class EnumConstraintTest extends VeryBaseTestCase
{
    /**
     * @dataProvider provideCases
     *
     * @param mixed       $value
     * @param list<mixed> $enum
     */
    public function testValueIsComparedWithTheEnumCases($value, array $enum, bool $expectedToBeValid): void
    {
        $constraint = new EnumConstraint();
        $schema = (object) ['enum' => $enum];

        $constraint->check($value, $schema);

        $this->assertSame($expectedToBeValid, $constraint->isValid());
    }

    /**
     * @return array<string, array{mixed, list<mixed>, bool}>
     */
    public static function provideCases(): array
    {
        return [
            'an integer matches an equal integer' => [1, [1], true],
            'a float matches a mathematically equal integer' => [1.0, [1], true],
            'an integer matches a mathematically equal float' => [1, [1.0], true],
            'a numeric string does not match an integer' => ['1', [1], false],
            'a numeric string does not match a float' => ['1.5', [1.5], false],
            'an integer does not match a numeric string' => [1, ['1'], false],
            'a numeric string in exponent notation does not match an integer' => ['1e0', [1], false],
        ];
    }
}
