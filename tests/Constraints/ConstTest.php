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

    public function getInvalidTests()
    {
        return array(
            array(
                '{"value":"foo"}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","const":"bar"}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{"value":5}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","const":6}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{"value":false}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"boolean","const":true}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
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
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{"value":"bar"}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","const":"bar"}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{"value":false}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"boolean","const":false}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{"value":true}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"boolean","const":true}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{"value":5}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","const":5}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
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
                            "const": {
                                    "foo": 12
                            }
                        }
                    }
                }'
            )
        );
    }
}
