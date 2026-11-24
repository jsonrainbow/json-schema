<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft04;

use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;
use JsonSchema\Validator;

class Draft04ConstraintTest extends VeryBaseTestCase
{
    /**
     * @dataProvider provideCases
     *
     * @param mixed $data
     */
    public function testKeywordsAreValidatedAsDefinedByDraft04(string $schema, $data, bool $expectedToBeValid): void
    {
        $decodedSchema = json_decode($schema);
        $decodedSchema->{'$schema'} = DraftIdentifiers::DRAFT_4;

        $validator = new Validator();
        $validator->validate($data, $decodedSchema, Constraint::CHECK_MODE_STRICT);

        self::assertSame($expectedToBeValid, $validator->isValid(), (string) json_encode($validator->getErrors()));
    }

    /**
     * @return array<string, array{string, mixed, bool}>
     */
    public static function provideCases(): array
    {
        return [
            'const is not a Draft-04 keyword' => ['{"const": 1}', 2, true],
            'contains is not a Draft-04 keyword' => ['{"contains": {"type": "string"}}', [1], true],
            'propertyNames is not a Draft-04 keyword' => ['{"propertyNames": {"maxLength": 1}}', (object) ['foo' => 1], true],
            'exclusiveMinimum without minimum is ignored' => ['{"exclusiveMinimum": 5}', 5, true],
            'exclusiveMaximum without maximum is ignored' => ['{"exclusiveMaximum": 5}', 5, true],
            'minimum is inclusive by default' => ['{"minimum": 5}', 5, true],
            'minimum is exclusive with exclusiveMinimum' => ['{"minimum": 5, "exclusiveMinimum": true}', 5, false],
            'minimum stays inclusive with exclusiveMinimum false' => ['{"minimum": 5, "exclusiveMinimum": false}', 5, true],
            'below minimum is invalid' => ['{"minimum": 5}', 4, false],
            'maximum is inclusive by default' => ['{"maximum": 5}', 5, true],
            'maximum is exclusive with exclusiveMaximum' => ['{"maximum": 5, "exclusiveMaximum": true}', 5, false],
            'above maximum is invalid' => ['{"maximum": 5}', 6, false],
            'a float with zero fractional part is not an integer' => ['{"type": "integer"}', 1.0, false],
            'an integer is an integer' => ['{"type": "integer"}', 1, true],
            'a numeric string is not equal to a number in enum' => ['{"enum": [1]}', '1', false],
            'a float is equal to a mathematically equal integer in enum' => ['{"enum": [1]}', 1.0, true],
            '$ref overrides sibling keywords' => ['{"definitions": {"a": {"type": "integer"}}, "$ref": "#/definitions/a", "maximum": 0}', 5, true],
        ];
    }

    public function testExclusiveMinimumReportsTheMinimum(): void
    {
        $schema = json_decode('{"$schema": "' . DraftIdentifiers::DRAFT_4 . '", "minimum": 5, "exclusiveMinimum": true}');
        $data = 5;

        $validator = new Validator();
        $validator->validate($data, $schema, Constraint::CHECK_MODE_STRICT);

        $errors = $validator->getErrors();
        self::assertCount(1, $errors);
        self::assertSame('exclusiveMinimum', $errors[0]['constraint']['name']);
        self::assertSame('Must have a minimum value greater than 5', $errors[0]['message']);
    }
}
