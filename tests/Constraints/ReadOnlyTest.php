<?php

namespace JsonSchema\Tests\Constraints;

class ReadOnlyTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield 'is readonly really required?' => [
            '{ "number": [] }',
            '{
              "type":"object",
              "properties":{
                "number":{"type":"string","readonly":true}
              }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "number": "1.4"
            }',
            '{
              "type":"object",
              "properties":{
                "number":{"type":"string","readonly":true}
              }
            }'
        ];
    }
}
