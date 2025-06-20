<?php

namespace JsonSchema\Tests\Constraints;

class RequireTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
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
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
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
        ];
    }
}
