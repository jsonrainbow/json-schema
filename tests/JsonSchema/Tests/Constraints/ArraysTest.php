<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class ArraysTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "array":[1,2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{
                      "type":"array",
                      "items":{"type":"number"}
                    }
                  }
                }'
            ),
            array(
                '{
                  "array":[1,2,null]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{
                      "type":"array",
                      "items":{"type":["number","boolean"]}
                    }
                  }
                }'
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{
                  "array":[1,2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{"type":"array"}
                  }
                }'
            ),
            array(
                '{
                  "array":[1,2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{
                      "type":"array",
                      "items":{"type":"number"},
                      "additionalItems": {"type": "string"}
                    }
                  }
                }'
            ),
        );
    }
}
