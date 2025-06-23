<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

class MinLengthMaxLengthTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "value":"w"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","minLength":2,"maxLength":4}
              }
            }'
        ];
        yield [
            '{
              "value":"wo7us"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","minLength":2,"maxLength":4}
              }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "value":"wo"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","minLength":2,"maxLength":4}
              }
            }'
        ];
        yield [
            '{
              "value":"wo7u"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","minLength":2,"maxLength":4}
              }
            }'
        ];
    }
}
