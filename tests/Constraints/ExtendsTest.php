<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class ExtendsTest extends BaseTestCase
{
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "name":"bruno",
              "age":50
            }',
            '{
                "properties": {
                    "name": {"type": "string"},
                    "age": {
                        "type": "integer",
                        "maximum": 120
                    }
                },
                "extends": {
                    "properties": {
                        "age": {"minimum": 70}
                    }
                }
            }'
        ];
        yield [
            '{
              "name":"bruno",
              "age":180
            }',
            '{
                "properties": {
                    "name": {"type": "string"},
                    "age": {
                        "type": "integer",
                        "maximum": 120
                    }
                },
                "extends": {
                    "properties": {
                        "age": {"minimum":70}
                    }
                }
            }'
        ];
        yield [
            '{"foo": 2, "bar": "baz"}',
            '{
                "properties": {
                    "bar": {"type": "integer", "required": true}
                },
                "extends": {
                    "properties": {
                        "foo": {"type": "string", "required": true}
                    }
                }
            }'
        ];
        yield [
            '{"bar": 2}',
            '{
                "properties": {
                    "bar": {"type": "integer", "required": true}
                },
                "extends" : [
                    {
                        "properties": {
                            "foo": {"type": "string", "required": true}
                        }
                    },
                    {
                        "properties": {
                            "baz": {"type": "null", "required": true}
                        }
                    }
                ]
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "name":"bruno",
              "age":80
            }',
            '{
                "properties": {
                    "name": {"type": "string"},
                    "age": {
                        "type": "integer",
                        "maximum": 120
                    }
                },
                "extends": {
                    "properties": {
                        "age": {"minimum": 70}
                    }
                }
            }'
        ];
        yield [
            '{"foo": "baz", "bar": 2}',
            '{
                "properties": {
                    "bar": {"type": "integer", "required": true}
                },
                "extends": {
                    "properties": {
                        "foo": {"type": "string", "required": true}
                    }
                }
            }'
        ];
        yield [
            '{"foo": "ick", "bar": 2, "baz": null}',
            '{
                "properties": {
                    "bar": {"type": "integer", "required": true}
                },
                "extends" : [
                    {
                        "properties": {
                            "foo": {"type": "string", "required": true}
                        }
                    },
                    {
                        "properties": {
                            "baz": {"type": "null", "required": true}
                        }
                    }
                ]
            }'
        ];
    }
}
