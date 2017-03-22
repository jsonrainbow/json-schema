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

    public function getInvalidTests()
    {
        return array(
            array(
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
                array(
                    array(
                        'property'   => '',
                        'pointer'    => '',
                        'message'    => 'The property additionalProp is not defined and the definition does not allow additional properties',
                        'constraint' => 'additionalProp',
                        'context' => Validator::ERROR_DOCUMENT_VALIDATION
                    )
                )
            ),
            array(
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
            ),
            array(
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
            ),
            array(
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
            ),
            array(
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
            ),
            array(
                '{
                  "prop1": "a",
                  "prop2": "b"
                }',
                '{
                  "type": "object",
                  "additionalProperties": false
                }'
            ),
        );
    }

    public function getValidTests()
    {
        return array(
            array(
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
            ),
            array(
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
            ),
            array(
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
            ),
            array(
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
            ),
            array(
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
            ),
            array(
                '{
                  "prop1": "a",
                  "prop2": "b"
                }',
                '{
                  "type": "object",
                  "additionalProperties": true
                }'
            ),
        );
    }
}
