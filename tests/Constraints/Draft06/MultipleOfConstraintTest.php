<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft06;

use JsonSchema\Constraints\Drafts\Draft06\MultipleOfConstraint;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;

class MultipleOfConstraintTest extends VeryBaseTestCase
{
    /**
     * @dataProvider provideNonPositiveMultipleOf
     *
     * @param int|float $multipleOf
     */
    public function testANonPositiveMultipleOfIsIgnored($multipleOf): void
    {
        $constraint = new MultipleOfConstraint();
        $schema = (object) ['multipleOf' => $multipleOf];
        $value = 5;

        $constraint->check($value, $schema);

        $this->assertSame([], $constraint->getErrors());
    }

    /**
     * @return array<string, array{int|float}>
     */
    public static function provideNonPositiveMultipleOf(): array
    {
        return [
            'zero' => [0],
            'zero as float' => [0.0],
            'negative integer' => [-2],
        ];
    }

    public function testAValueThatIsNotAMultipleIsReported(): void
    {
        $constraint = new MultipleOfConstraint();
        $schema = (object) ['multipleOf' => 2];
        $value = 5;

        $constraint->check($value, $schema);

        $this->assertCount(1, $constraint->getErrors());
    }
}
