<?php

class OptionalPropertyTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"string","optional":false}
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
                  "number": "1.4"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"string","optional":false}
                  }
                }'
            )
        );
    }
}
