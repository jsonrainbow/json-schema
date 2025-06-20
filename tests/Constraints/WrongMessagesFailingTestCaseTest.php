<?php

namespace JsonSchema\Tests\Constraints;

class WrongMessagesFailingTestCaseTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
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
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
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
        ];
    }
}
