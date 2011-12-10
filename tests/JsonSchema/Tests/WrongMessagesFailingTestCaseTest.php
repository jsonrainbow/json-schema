<?php

namespace JsonSchema\Tests;

class WrongMessagesFailingTestCaseTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "stringOrNumber":4.8,
                  "booleanOrNull":["A","B"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "stringOrNumber":{"type":["string","number"]},
                    "booleanOrNull":{"type":["boolean","null"]}
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
                  "stringOrNumber":4.8,
                  "booleanOrNull":true
                }',
                '{
                  "type":"object",
                  "properties":{
                    "stringOrNumber":{"type":["string","number"]},
                    "booleanOrNull":{"type":["boolean","null"]}
                  }
                }'
            )
        );
    }
}
