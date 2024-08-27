<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class MinItemsMaxItemsTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
                '{
                  "value":[2]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"array","minItems":2,"maxItems":4}
                  }
                }'
            ],
            [
                '{
                  "value":[2,2,5,8,5]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"array","minItems":2,"maxItems":4}
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
                  "value":[2,2]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"array","minItems":2,"maxItems":4}
                  }
                }'
            ],
            [
                '{
                  "value":[2,2,5,8]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"array","minItems":2,"maxItems":4}
                  }
                }'
            ]
        ];
    }
}
