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

    public function getInvalidTests()
    {
        return [
            [
                '{"bar": 1}',
                '{
                    "dependencies": {"bar": "foo"}
                }'
            ],
            [
                '{"bar": 1}',
                '{
                    "dependencies": {"bar": ["foo"]}
                }'
            ],
            [
                '{"bar": 1, "foo": 1}',
                '{
                    "dependencies": {"bar": ["foo", "baz"]}
                }'
            ],
            [
                '{"bar": 1, "foo": 1}',
                '{
                    "dependencies": {"bar": {
                        "properties": {
                            "foo": {"type": "string"}
                        }
                    }}
                }'
            ],
            [
                '{"bar": 1}',
                '{
                    "dependencies": {"bar": {
                        "properties": {
                            "foo": {"type": "integer", "required": true}
                        }
                    }}
                }'
            ],
            [
                '{"bar": 1}',
                '{
                    "dependencies": {"bar": {
                        "properties": {
                            "foo": {"type": "integer"}
                        },
                        "required": ["foo"]
                    }}
                }'
            ],
            [
                '{"bar": true, "foo": "ick"}',
                '{
                    "dependencies": {"bar": {
                        "properties": {
                            "bar": {"type": "integer"},
                            "foo": {"type": "integer"}
                        }
                    }}
                }'
            ]
        ];
    }

    public function getValidTests()
    {
        return [
            [
                '{}',
                '{
                    "dependencies": {"bar": "foo"}
                }'
            ],
            [
                '{"foo": 1}',
                '{
                    "dependencies": {"bar": "foo"}
                }'
            ],
            [
                '"foo"',
                '{
                    "dependencies": {"bar": "foo"}
                }'
            ],
            [
                '{"bar": 1, "foo": 1}',
                '{
                    "dependencies": {"bar": "foo"}
                }'
            ],
            [
                '{"bar": 1, "foo": 1, "baz": 1}',
                '{
                    "dependencies": {"bar": ["foo", "baz"]}
                }'
            ],
            [
                '{}',
                '{
                    "dependencies": {"bar": ["foo", "baz"]}
                }'
            ],
            [
                '{"foo": 1, "baz": 1}',
                '{
                    "dependencies": {"bar": ["foo", "baz"]}
                }'
            ],
            [
                '{"bar": 1}',
                '{
                    "dependencies": {"bar": {
                        "properties": {
                            "foo": {"type": "integer"}
                        }
                    }}
                }'
            ],
            [
                '{"bar": 1, "foo": 1}',
                '{
                    "dependencies": {"bar": {
                        "properties": {
                            "bar": {"type": "integer"},
                            "foo": {"type": "integer"}
                        }
                    }}
                }'
            ]
        ];
    }
}
