<?php

class AdditionalPropertiesTest extends BaseTestCase
{
    public function getInvalidTests()
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
                  },
                  "additionalProperties": false
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
                  "additionalProperties": false
                }',
                JsonSchema::CHECK_MODE_TYPE_CAST
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
                }',
                JsonSchema::CHECK_MODE_TYPE_CAST
            )
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
                }',
                JsonSchema::CHECK_MODE_TYPE_CAST
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
            )
        );
    }
}
