<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class DependenciesTest extends BaseTestCase
{
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
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
