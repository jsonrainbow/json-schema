<?php

namespace JsonSchema\Tests;

class MinLengthMaxLengthTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":"w"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","minLength":2,"maxLength":4}
                  }
                }'
            ),
            array(
                '{
                  "value":"wo7us"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","minLength":2,"maxLength":4}
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
                  "value":"wo"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","minLength":2,"maxLength":4}
                  }
                }'
            ),
            array(
                '{
                  "value":"wo7u"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","minLength":2,"maxLength":4}
                  }
                }'
            ),

        );
    }
}
