<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class RequireTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
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
            ]
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
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
            ]
        ];
    }
}
