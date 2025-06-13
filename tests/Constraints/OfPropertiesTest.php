<?php
/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;

/**
 * Class OfPropertiesTest
 */
class OfPropertiesTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getValidTests(): \Generator
    {
        yield [
            '{"prop1": "abc"}',
            '{
              "type": "object",
              "properties": {
                "prop1": {"type": "string"},
                "prop2": {
                  "oneOf": [
                    {"type": "number"},
                    {"type": "string"}
                  ]
                }
              },
              "required": ["prop1"]
            }'
        ];
        yield [
            '{"prop1": "abc", "prop2": 23}',
            '{
              "type": "object",
              "properties": {
                "prop1": {"type": "string"},
                "prop2": {
                  "oneOf": [
                    {"type": "number"},
                    {"type": "string"}
                  ]
                }
              },
              "required": ["prop1"]
            }'
        ];
    }

    public function getInvalidTests(): \Generator
    {
        yield [
            '{"prop1": "abc", "prop2": []}',
            '{
              "type": "object",
              "properties": {
                "prop1": {"type": "string"},
                "prop2": {
                  "oneOf": [
                    {"type": "number"},
                    {"type": "string"}
                  ]
                }
              },
              "required": ["prop1"]
            }',
            null,
            [
                [
                    'property'   => 'prop2',
                    'pointer'    => '/prop2',
                    'message'    => 'Array value found, but a string is required',
                    'constraint' => [
                        'name' => 'type',
                        'params' => [
                            'expected'   => 'a string',
                            'found'      => 'array'
                        ]
                    ],
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ],
                [
                    'property'   => 'prop2',
                    'pointer'    => '/prop2',
                    'message'    => 'Array value found, but a number is required',
                    'constraint' => [
                        'name' => 'type',
                        'params' => [
                            'expected'   => 'a number',
                            'found'      => 'array'
                        ]
                    ],
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ],
                [
                    'property'   => 'prop2',
                    'pointer'    => '/prop2',
                    'message'    => 'Failed to match exactly one schema',
                    'constraint' => [
                        'name' => 'oneOf',
                        'params' => []
                    ],
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ],
            ],
        ];
        yield [
            '{"prop1": [1,2]}',
            '{
              "type": "object",
              "properties": {
                "prop1": {
                  "oneOf": [
                    {
                      "type": "string",
                      "pattern": "^[a-z]*$"
                    },
                    {
                      "type": "string",
                      "pattern": "^[A-Z]*$"
                    }
                  ]
                }
              }
            }'
        ];
        yield [
            '{"prop1": [1,2]}',
            '{
              "type": "object",
              "properties": {
                "prop1": {
                  "anyOf": [
                    {
                      "type": "string",
                      "pattern": "^[A-Z]*$"
                    }
                  ]
                }
              }
            }'
        ];
        yield[
            '{"prop1": [1,2]}',
            '{
              "type": "object",
              "properties": {
                "prop1": {
                  "anyOf": [
                    {
                      "type": "number"
                    },
                    {
                      "type": "string",
                      "pattern": "^[A-Z]*$"
                    }
                  ]
                }
              }
            }'
        ];
        yield[
            '{"prop1": [1,2]}',
            '{
              "type": "object",
              "properties": {
                "prop1": {
                  "anyOf": [
                    {
                      "type": "string"
                    },
                    {
                      "type": "string",
                      "pattern": "^[A-Z]*$"
                    }
                  ]
                }
              }
            }'
        ];
        yield[
            '{"prop1": [1,2]}',
            '{
              "type": "object",
              "properties": {
                "prop1": {
                  "anyOf": [
                    {
                      "type": "string",
                      "pattern": "^[a-z]*$"
                    },
                    {
                      "type": "string",
                      "pattern": "^[A-Z]*$"
                    }
                  ]
                }
              }
            }'
        ];
        yield [
            '{"prop1": [1,2]}',
            '{
              "type": "object",
              "properties": {
                "prop1": {
                  "anyOf": [
                    {
                      "type": "number"
                    },
                    {
                      "type": "string"
                    },
                    {
                      "type": "string"
                    }
                  ]
                }
              }
            }'
        ];
    }

    public function testNoPrematureAnyOfException(): void
    {
        $schema = json_decode('{
            "type": "object",
            "properties": {
                "propertyOne": {
                    "anyOf": [
                        {"type": "number"},
                        {"type": "string"}
                    ]
                }
            }
        }');
        $data = json_decode('{"propertyOne":"ABC"}');

        $v = new Validator();
        $v->validate($data, $schema, Constraint::CHECK_MODE_EXCEPTIONS);
        $this->assertTrue($v->isValid());
    }

    public function testNoPrematureOneOfException(): void
    {
        $schema = json_decode('{
            "type": "object",
            "properties": {
                "propertyOne": {
                    "oneOf": [
                        {"type": "number"},
                        {"type": "string"}
                    ]
                }
            }
        }');
        $data = json_decode('{"propertyOne":"ABC"}');

        $v = new Validator();
        $v->validate($data, $schema, Constraint::CHECK_MODE_EXCEPTIONS);
        $this->assertTrue($v->isValid());
    }
}
