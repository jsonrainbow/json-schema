<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class NumberAndIntegerTypesTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
                '{
                  "integer": 1.4
                }',
                '{
                  "type":"object",
                  "properties":{
                    "integer":{"type":"integer"}
                  }
                }'
            ],
            [
                '{"integer": 1.001}',
                '{
                    "type": "object",
                    "properties": {
                        "integer": {"type": "integer"}
                    }
                }'
            ],
            [
                '{"integer": true}',
                '{
                    "type": "object",
                    "properties": {
                        "integer": {"type": "integer"}
                    }
                }'
            ],
            [
                '{"number": "x"}',
                '{
                    "type": "object",
                    "properties": {
                        "number": {"type": "number"}
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
                  "integer": 1
                }',
                '{
                  "type":"object",
                  "properties":{
                    "integer":{"type":"integer"}
                  }
                }'
            ],
            [
                '{
                  "number": 1.4
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            ],
            [
                '{"number": 1e5}',
                '{
                    "type": "object",
                    "properties": {
                        "number": {"type": "number"}
                    }
                }'
            ],
            [
                '{"number": 1}',
                '{
                    "type": "object",
                    "properties": {
                        "number": {"type": "number"}

                    }
                }'
            ],
            [
                '{"number": -49.89}',
                '{
                    "type": "object",
                    "properties": {
                        "number": {
                          "type": "number",
                          "multipleOf": 0.01
                        }
                    }
                }'
            ]
        ];
    }
}
