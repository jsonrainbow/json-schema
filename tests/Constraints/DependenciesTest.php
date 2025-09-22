<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\DraftIdentifiers;

class DependenciesTest extends BaseTestCase
{
    /** @var string */
    protected $schemaSpec = DraftIdentifiers::DRAFT_3;
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{"bar": 1}',
            '{
                "dependencies": {"bar": "foo"}
            }'
        ];
        yield [
            '{"bar": 1}',
            '{
                "dependencies": {"bar": ["foo"]}
            }'
        ];
        yield [
            '{"bar": 1, "foo": 1}',
            '{
                "dependencies": {"bar": ["foo", "baz"]}
            }'
        ];
        yield [
            '{"bar": 1, "foo": 1}',
            '{
                "dependencies": {"bar": {
                    "properties": {
                        "foo": {"type": "string"}
                    }
                }}
            }'
        ];
        yield [
            '{"bar": 1}',
            '{
                "dependencies": {"bar": {
                    "properties": {
                        "foo": {"type": "integer", "required": true}
                    }
                }}
            }'
        ];
        yield [
            '{"bar": 1}',
            '{
                "dependencies": {"bar": {
                    "properties": {
                        "foo": {"type": "integer"}
                    },
                    "required": ["foo"]
                }}
            }'
        ];
        yield [
            '{"bar": true, "foo": "ick"}',
            '{
                "dependencies": {"bar": {
                    "properties": {
                        "bar": {"type": "integer"},
                        "foo": {"type": "integer"}
                    }
                }}
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{}',
            '{
                "dependencies": {"bar": "foo"}
            }'
        ];
        yield [
            '{"foo": 1}',
            '{
                "dependencies": {"bar": "foo"}
            }'
        ];
        yield [
            '"foo"',
            '{
                "dependencies": {"bar": "foo"}
            }'
        ];
        yield [
            '{"bar": 1, "foo": 1}',
            '{
                "dependencies": {"bar": "foo"}
            }'
        ];
        yield [
            '{"bar": 1, "foo": 1, "baz": 1}',
            '{
                "dependencies": {"bar": ["foo", "baz"]}
            }'
        ];
        yield [
            '{}',
            '{
                "dependencies": {"bar": ["foo", "baz"]}
            }'
        ];
        yield [
            '{"foo": 1, "baz": 1}',
            '{
                "dependencies": {"bar": ["foo", "baz"]}
            }'
        ];
        yield [
            '{"bar": 1}',
            '{
                "dependencies": {"bar": {
                    "properties": {
                        "foo": {"type": "integer"}
                    }
                }}
            }'
        ];
        yield [
            '{"bar": 1, "foo": 1}',
            '{
                "dependencies": {"bar": {
                    "properties": {
                        "bar": {"type": "integer"},
                        "foo": {"type": "integer"}
                    }
                }}
            }'
        ];
    }
}
