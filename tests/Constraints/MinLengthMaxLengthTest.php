<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class MinLengthMaxLengthTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
                '{
                  "value":"w"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","minLength":2,"maxLength":4}
                  }
                }'
            ],
            [
                '{
                  "value":"wo7us"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","minLength":2,"maxLength":4}
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
                  "value":"wo"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","minLength":2,"maxLength":4}
                  }
                }'
            ],
            [
                '{
                  "value":"wo7u"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","minLength":2,"maxLength":4}
                  }
                }'
            ],
        ];
    }
}
