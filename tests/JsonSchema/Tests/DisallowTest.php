<?php

namespace JsonSchema\Tests;

class DisallowTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":" The xpto is weird"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"any",
                      "disallow":{"type":"string","pattern":"xpto"}
                    }
                  }
                }'
            ),
            array(
                '{
                  "value":null
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"any",
                      "disallow":{"type":"null"}
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
                '{
                  "value":" The xpto is weird"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"any",
                      "disallow":{"type":"string","pattern":"^xpto"}
                    }
                  }
                }'
            ),
            array(
                '{
                  "value":1
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"any",
                      "disallow":{"type":"null"}
                    }
                  }
                }'
            )
        );
    }
}
