<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft2020;

use Generator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Drafts\Draft2020\FormatConstraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;

class FormatConstraintTest extends VeryBaseTestCase
{
    /**
     * @dataProvider getCheckModes
     */
    public function testInvalidFormat(int $checkMode, bool $expectedValid): void
    {
        $validator = new FormatConstraint(new Factory(null, null, $checkMode));
        $schema = new \stdClass();
        $schema->format = 'date';
        $value = 'not a date';

        $validator->check($value, $schema);

        $this->assertSame($expectedValid, $validator->isValid());
    }

    public function getCheckModes(): Generator
    {
        yield 'Format is an annotation by default' => [Constraint::CHECK_MODE_NORMAL, true];
        yield 'Format is asserted when enabled' => [Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_FORMAT_ASSERTION, false];
        yield 'Disabling format takes precedence over enabling format assertion' => [Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_FORMAT_ASSERTION | Constraint::CHECK_MODE_DISABLE_FORMAT, true];
    }
}
