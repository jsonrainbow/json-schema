<?php

class PhpTypeCastModeTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "a":"c"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"number"}
                  }
                }',
                null,
                array(
                    array(
                        'property' => 'a',
                        'message'  => 'string value found, but a number is required'
                    )
                )
            ),
            array(
                '{
                  "a":"9"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"integer","maximum":8}
                  }
                }'
            )
        );
    }
    
    public function getValidTests()
    {
        return array(
            array(
                '{
                  "a":"9"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"integer","maximum":8.0}
                  }
                }',
                JsonSchema::CHECK_MODE_TYPE_CAST
            ),
            array(
                '{
                  "a":"9"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"number"}
                  }
                }',
                JsonSchema::CHECK_MODE_TYPE_CAST
            ),
        );
    }
}
