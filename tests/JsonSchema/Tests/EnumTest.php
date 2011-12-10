<?php

namespace JsonSchema\Tests;

class EnumTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":"Morango"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
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
                  "value":"Abacate"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
                  },
                  "additionalProperties":false
                }'
            )
        );
    }
}
