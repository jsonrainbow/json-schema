<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class WrongMessagesFailingTestCaseTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
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
            ]
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
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
            ]
        ];
    }
}
