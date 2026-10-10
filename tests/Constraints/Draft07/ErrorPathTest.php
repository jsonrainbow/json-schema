<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft07;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;
use JsonSchema\Validator;

class ErrorPathTest extends VeryBaseTestCase
{
    private const SCHEMA_URI = 'http://json-schema.org/draft-07/schema#';

    public function testNestedPropertyErrorsAreReportedAtTheirPath(): void
    {
        $errors = $this->validate(
            '{"type": "object", "required": ["bar"], "properties": {"foo": {"type": "integer"}, "n": {"type": "object", "properties": {"x": {"type": "integer"}}}}}',
            '{"foo": "bar", "n": {"x": "y"}}'
        );

        $this->assertSame(['/bar', '/foo', '/n/x'], array_column($errors, 'pointer'));
        $this->assertSame(['bar', 'foo', 'n.x'], array_column($errors, 'property'));
    }

    public function testItemErrorsAreReportedAtTheirIndex(): void
    {
        $errors = $this->validate(
            '{"properties": {"list": {"items": {"type": "integer"}}, "tuple": {"items": [{"type": "integer"}], "additionalItems": {"type": "integer"}}}}',
            '{"list": [1, "a"], "tuple": ["b", "c"]}'
        );

        $this->assertSame(['/list/1', '/tuple/0', '/tuple/1'], array_column($errors, 'pointer'));
    }

    public function testPatternPropertyErrorsAreReportedAtThePropertyPath(): void
    {
        $errors = $this->validate(
            '{"patternProperties": {"^p": {"type": "integer"}}}',
            '{"p1": "a", "p2": "b"}'
        );

        $this->assertSame(['/p1', '/p2'], array_column($errors, 'pointer'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function validate(string $schema, string $data): array
    {
        $schema = json_decode($schema);
        $schema->{'$schema'} = self::SCHEMA_URI;
        $data = json_decode($data);

        $validator = new Validator(new Factory(null, null, Constraint::CHECK_MODE_STRICT));
        $validator->validate($data, $schema);

        return $validator->getErrors();
    }
}
