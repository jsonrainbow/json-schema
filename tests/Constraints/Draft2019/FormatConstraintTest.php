<?php

declare(strict_types=1);

namespace Constraints\Draft2019;

use Generator;
use JsonSchema\Constraints\Drafts\Draft2019\FormatConstraint;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;

class FormatConstraintTest extends VeryBaseTestCase
{
    /**
     * @dataProvider getInvalidFormats
     */
    public function testInvalidFormat($string, $format): void
    {
        $validator = new FormatConstraint();
        $schema = new \stdClass();
        $schema->format = $format;

        $validator->check($string, $schema);
        $this->assertCount(1, $validator->getErrors(), 'Expected 1 error');
    }

    public function getInvalidFormats(): Generator
    {
        yield 'Date-time format with value containing null byte' => ['2020-01-01T12:34:56\x00', 'date'];

        yield 'Date format with value containing null byte' => ['2020-01-01\x00', 'date'];

        yield 'Time format with value containing null byte' => ['13:37:00\x00', 'time'];
    }
}
