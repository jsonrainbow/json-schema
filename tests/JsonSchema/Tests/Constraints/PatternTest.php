<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class PatternTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"^cat"}
                  },
                  "additionalProperties":false
                }'
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"tes$"}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"cat"}
                  },
                  "additionalProperties":false
                }'
            )
        );
    }
}
