<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

class MinLengthMaxLengthMultiByteTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    protected function setUp(): void
    {
        if (!extension_loaded('mbstring')) {
            $this->markTestSkipped('mbstring extension is not available');
        }
    }

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "value":"☀"
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
              "value":"☀☁☂☃☺"
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
              "value":"☀☁"
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
              "value":"☀☁☂☃"
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
