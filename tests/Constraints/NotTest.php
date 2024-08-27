<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class NotTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
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
            ],
            [ // check that a missing, required property is correctly validated
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
            ]
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
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
            ],
            [
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
            ],
            [ // check that a missing, non-required property isn't validated
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
            ]
        ];
    }
}
