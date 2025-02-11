<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Validator;

class AdditionalPropertiesTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
                '{
                  "prop1": "a",
                  "prop2": "b"
                }',
                '{
                  "type": "object",
                  "additionalProperties": false
                }'
            ],
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
                '{
                  "prop1": "a",
                  "prop2": "b"
                }',
                '{
                  "type": "object",
                  "additionalProperties": true
                }'
            ],
            [
                '{
                  "prop1": {
                    "prop2": "a"
                  }
                }',
                '{
                  "type": "object",
                  "additionalProperties": {
                    "type": "object",
                    "properties": {
                      "prop2": {
                        "type": "string"
                      }
                    }
                  }
                }'
            ],
            [
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
            ],
        ];
    }
}
