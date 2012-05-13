<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class TupleTypingTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "tupleTyping":[2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "tupleTyping":{
                      "type":"array",
                      "items":[
                        {"type":"string"},
                        {"type":"number"}
                      ]
                    }
                  }
                }'
            ),
            array(
                '{
                  "tupleTyping":["2",2,3]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "tupleTyping":{
                      "type":"array",
                      "items":[
                        {"type":"string"},
                        {"type":"number"}
                      ] ,
                      "additionalProperties":false
                    }
                  }
                }'
            ),
            array(
                '{
                  "tupleTyping":["2",2,3]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "tupleTyping":{
                      "type":"array",
                      "items":[
                        {"type":"string"},
                        {"type":"number"}
                      ] ,
                      "additionalProperties":{"type":"string"}
                    }
                  }
                }'
            ),
            array(
                '{
                  "tupleTyping":["2"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "tupleTyping":{
                      "type":"array",
                      "items":[
                        {"type":"string"},
                        {"type":"number"},
                        {"required":true}
                      ]
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
                  "tupleTyping":["2"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "tupleTyping":{
                      "type":"array",
                      "items":[
                        {"type":"string"},
                        {"type":"number","required":false},
                        {"type":"number","required":false}
                      ]
                    }
                  }
                }'
            )
        );
    }
}
