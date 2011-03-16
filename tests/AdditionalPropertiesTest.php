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
                  "additionalProp":2
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
                  },
                  "additionalProperties": {"type":"string"}
                }'
            )
        );
    }
}
