<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

class NotTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
                "x": [1, 2]
            }',
            '{
                "properties": {
                    "x": {
                        "not": {
                            "type": "array",
                            "items": {"type": "integer"},
                            "minItems": 2
                        }
                    }
                }
            }'
        ];
        yield 'check that a missing, required property is correctly validated' => [
            '{"y": "foo"}',
            '{
                "type": "object",
                "required": ["x"],
                "properties": {
                    "x": {
                        "not": {
                            "type": "null"
                        }
                    }
                }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
                "x": [1]
            }',
            '{
                "properties": {
                    "x": {
                        "not": {
                            "type": "array",
                            "items": {"type": "integer"},
                            "minItems": 2
                        }
                    }
                }
            }'
        ];
        yield [
            '{
                "x": ["foo", 2]
            }',
            '{
                "properties": {
                    "x": {
                        "not": {
                            "type": "array",
                            "items": {"type": "integer"},
                            "minItems": 2
                        }
                    }
                }
            }'
        ];
        yield "check that a missing, non-required property isn't validated" => [
            '{"y": "foo"}',
            '{
                "type": "object",
                "properties": {
                    "x": {
                        "not": {
                            "type": "null"
                        }
                    }
                }
            }'
        ];
    }
}
