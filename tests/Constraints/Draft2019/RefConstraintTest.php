<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft2019;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;
use JsonSchema\Validator;

class RefConstraintTest extends VeryBaseTestCase
{
    private const DIALECT = 'https://json-schema.org/draft/2019-09/schema';

    public function testAnUnresolvableRefIsReportedInsteadOfSilentlyPassing(): void
    {
        $validator = new Validator();
        $data = new \stdClass();
        $schema = json_decode('{"$schema": "' . self::DIALECT . '", "$ref": "#/$defs/nonExistent"}');

        $validator->validate($data, $schema, Constraint::CHECK_MODE_STRICT);

        self::assertFalse($validator->isValid());
        $errors = $validator->getErrors();
        self::assertCount(1, $errors);
        self::assertSame('unresolvableRef', $errors[0]['constraint']['name']);
    }

    public function testAResolvableRefStillValidates(): void
    {
        $validator = new Validator();
        $data = json_decode('{"child": 1}');
        $schema = json_decode(
            '{"$schema": "' . self::DIALECT . '", "$defs": {"num": {"type": "integer"}},'
            . ' "properties": {"child": {"$ref": "#/$defs/num"}}}'
        );

        $validator->validate($data, $schema, Constraint::CHECK_MODE_STRICT);

        self::assertTrue($validator->isValid(), (string) json_encode($validator->getErrors()));
    }
}
