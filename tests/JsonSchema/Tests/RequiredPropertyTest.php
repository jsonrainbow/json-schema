<?php

namespace JsonSchema\Tests;

class RequiredPropertyTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"string","required":true}
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
                    "number":{"type":"string","required":true}
                  }
                }'
            )
        );
    }
}
