<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Validator;

class ArrayAsObjectModeTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "array":[1,2,"a"],
                  "obj": { "meaning": 42 }
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{
                      "type":"array",
                      "items":{"type":"number"}
                    },
                    "obj":{
                      "type":"object",
                      "properties": {
                        "meaning":{"type":"number"},
                        "life":{"type":"string"}
                      },
                      "required":["meaning","life"]
                    }
                  }
                }',
                Validator::CHECK_MODE_ARR_AS_OBJ
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{
                  "somenull": null,
                  "somebool": true,
                  "someint": 42,
                  "somefloat": 3.14159,
                  "somestring": "asdf",
                  "somearr": ["one","two","three"]
                }',
                '{
                  "type": "object",
                  "properties": {
                    "somenull": {"type": "null"},
                    "somebool": {"type": "boolean"},
                    "someint": {"type": "number"},
                    "somefloat": {"type": "number"},
                    "somestring": {"type": "string"},
                    "somearr": {"type": "array"}
                  }
                }',
                Validator::CHECK_MODE_ARR_AS_OBJ,
            ),
        );
    }
}
