<?php

namespace JsonSchema\Tests;

class RequireTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "state":"DF"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "state":{"type":"string","requires":"city"},
                    "city":{"type":"string"}
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
                  "state":"DF",
                  "city":"Brasília"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "state":{"type":"string","requires":"city"},
                    "city":{"type":"string"}
                  }
                }'
            )
        );
    }
}
