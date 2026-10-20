<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft06;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\UnresolvableJsonPointerException;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;
use JsonSchema\Validator;

class RefConstraintTest extends VeryBaseTestCase
{
    private const DIALECT = 'http://json-schema.org/draft-06/schema#';

    public function testAnUnresolvableRefThrowsInsteadOfSilentlyPassing(): void
    {
        $validator = new Validator();
        $data = new \stdClass();
        $schema = json_decode('{"$schema": "' . self::DIALECT . '", "$ref": "#/$defs/nonExistent"}');

        $this->expectException(UnresolvableJsonPointerException::class);

        $validator->validate($data, $schema, Constraint::CHECK_MODE_STRICT);
    }

    public function testAnUnresolvableRefInsideAnApplicatorIsNotSwallowed(): void
    {
        $validator = new Validator();
        $data = new \stdClass();
        $schema = json_decode('{"$schema": "' . self::DIALECT . '", "anyOf": [{"$ref": "#/$defs/nonExistent"}, {}]}');

        $this->expectException(UnresolvableJsonPointerException::class);

        $validator->validate($data, $schema, Constraint::CHECK_MODE_STRICT);
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
