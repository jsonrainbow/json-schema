<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class EnumTest extends BaseTestCase
{
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
                '{
                  "value":"Morango"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
                  },
                  "additionalProperties":false
                }'
            ],
            [
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"string",
                      "enum":["Abacate","Manga","Pitanga"],
                      "required":true
                    }
                  },
                  "additionalProperties":false
                }'
            ],
            [
                '{"value": "4"}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "integer", "enum": [1, 2, 3]
                        }
                    },
                    "additionalProperties": false
                }'
            ],
            [
                '{"value": {"foo": false}}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "any", "enum": [6, "foo", [], true, {"foo": 12}]
                        }
                    },
                    "additionalProperties": false
                }'
            ],
            [
                '{
                    "value": {
                        "foo": "12"
                    }
                }',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "any", 
                            "enum": [
                                6, 
                                "foo", 
                                [], 
                                true, 
                                {
                                    "foo": 12
                                }
                            ]
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
                  "value":"Abacate"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
                  },
                  "additionalProperties":false
                }'
            ],
            [
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
                  },
                  "additionalProperties":false
                }'
            ],
            [
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"string",
                      "enum":["Abacate","Manga","Pitanga"],
                      "required":false
                    }
                  },
                  "additionalProperties":false
                }'
            ],
            [
                '{"value": 1}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "enum": [1, 2, 3]}
                    }
                }'
            ],
            [
                '{"value": []}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "any", "enum": [6, "foo", [], true, {"foo": 12}]}
                    },
                    "additionalProperties": false
                }'
            ],
            [
                '{
                    "value": {
                        "foo": 12
                    }
                }',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "any",
                            "enum": [
                                6,
                                "foo",
                                [],
                                true,
                                {
                                    "foo": 12
                                }
                            ]
                        }
                    }
                }'
            ],
            'Numeric values with mathematical equality are considered valid' => [
                'data' => '12',
                'schema' => '{
                    "type": "any",
                    "enum": [
                        12.0
                    ]
                }'
            ]
        ];
    }
}
