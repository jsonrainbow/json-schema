<?php

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Validator;

class AdditionalPropertiesTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
                '{
                  "prop":"1",
                  "patternProp":"3",
                  "additionalProp":"2"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "prop":{"type":"string"}
                  },
                  "patternProperties":{
                      "^patternProp$":{"type":"string"}
                  },
                  "additionalProperties": false
                }',
                null,
                [
                    [
                        'property'   => '',
                        'pointer'    => '',
                        'message'    => 'The property additionalProp is not defined and the definition does not allow additional properties',
                        'constraint' => [
                            'name' => 'additionalProp',
                            'params' => [
                                'property' => 'additionalProp'
                            ]
                        ],
                        'context' => Validator::ERROR_DOCUMENT_VALIDATION
                    ]
                ]
            ];
        yield [
                '{
                  "prop":"1",
                  "additionalProp":"2"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "prop":{"type":"string"}
                  },
                  "additionalProperties": false
                }'
            ];
        yield [
                '{
                  "prop":"1",
                  "additionalProp":2
                }',
                '{
                  "type":"object",
                  "properties":{
                    "prop":{"type":"string"}
                  },
                  "additionalProperties": {"type":"string"}
                }'
            ];
        yield [
                '{
                  "prop":"1",
                  "additionalProp":2
                }',
                '{
                  "type":"object",
                  "properties":{
                    "prop":{"type":"string"}
                  },
                  "additionalProperties": {"type":"string"}
                }'
            ];
        yield [
                '{
                  "prop1": "a",
                  "prop2": "b"
                }',
                '{
                  "type": "object",
                  "additionalProperties": {
                    "type": "boolean"
                  }
                }'
            ];
        yield [
                '{
                  "prop1": "a",
                  "prop2": "b"
                }',
                '{
                  "type": "object",
                  "additionalProperties": false
                }'
            ];
    }

    public function getValidTests(): \Generator
    {
        yield [
                '{
                  "prop":"1",
                  "additionalProp":"2"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "prop":{"type":"string"}
                  }
                }'
            ];
        yield [
                '{
                  "prop":"1",
                  "additionalProp":"2"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "prop":{"type":"string"}
                  }
                }'
        ];
        yield [
                '{
                  "prop":"1",
                  "additionalProp":"2"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "prop":{"type":"string"}
                  },
                  "additionalProperties": {"type":"string"}
                }'
        ];
        yield [
                '{
                  "prop":"1",
                  "additionalProp":[]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "prop":{"type":"string"}
                  },
                  "additionalProperties": true
                }'
        ];
        yield [
                '{
                  "prop1": "a",
                  "prop2": "b"
                }',
                '{
                  "type": "object",
                  "additionalProperties": {
                    "type": "string"
                  }
                }'
        ];
        yield [
                '{
                  "prop1": "a",
                  "prop2": "b"
                }',
                '{
                  "type": "object",
                  "additionalProperties": true
                }'
        ];
        yield 'additional property casted into int when actually is numeric string (#784)' => [
                '{
                    "prop1": {
                        "123": "a"
                    }
                }',
                '{
                    "type": "object",
                    "additionalProperties": {
                        "type": "object",
                        "properties": {
                            "123": {
                                "type": "string"
                            }
                        }
                    }
                }'
            ];
    }
}
