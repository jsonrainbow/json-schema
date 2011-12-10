<?php

namespace JsonSchema\Tests;

class PatternTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"^cat"}
                  },
                  "additionalProperties":false
                }'
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"tes$"}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"cat"}
                  },
                  "additionalProperties":false
                }'
            )
        );
    }
}
