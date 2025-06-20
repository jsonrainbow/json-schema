<?php

namespace JsonSchema\Tests\Constraints;

class UnionWithNullValueTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "stringOrNumber":null,
              "booleanOrNull":null
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
              "stringOrNumber":12,
              "booleanOrNull":null
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
