<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class ConstTest extends BaseTestCase
{
    protected $schemaSpec = 'http://json-schema.org/draft-06/schema#';
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            'Object with inner string value' => [
                '{"value":"foo"}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","const":"bar"}
                  },
                  "additionalProperties":false
                }'
            ],
            'Object with inner integer value' => [
                '{"value":5}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","const":6}
                  },
                  "additionalProperties":false
                }'
            ],
            'Object with inner boolean value' => [
                '{"value":false}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"boolean","const":true}
                  },
                  "additionalProperties":false
                }'
            ],
            'Object with inner numerical string value' => [
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
                            "const": {
                                "foo": 12
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
            'String value' => [
                '{"value":"bar"}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","const":"bar"}
                  },
                  "additionalProperties":false
                }'
            ],
            'Boolean(false) value' => [
                '{"value":false}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"boolean","const":false}
                  },
                  "additionalProperties":false
                }'
            ],
            'Boolean(true) value' => [
                '{"value":true}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"boolean","const":true}
                  },
                  "additionalProperties":false
                }'
            ],
            'Integer value' => [
                '{"value":5}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","const":5}
                  },
                  "additionalProperties":false
                }'
            ],
            'Object with inner integer value' => [
                '{
                    "value": {
                        "foo": 12
                    }
                }',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "object",
                            "const": {
                                    "foo": 12
                            }
                        }
                    }
                }'
            ]
        ];
    }
}
