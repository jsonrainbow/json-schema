<?php

namespace JsonSchema\Tests;

class UnionTypesTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "stringOrNumber":4.8,
                  "booleanOrNull":5
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
                  "booleanOrNull":false
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
