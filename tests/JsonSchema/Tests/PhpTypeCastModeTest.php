<?php

namespace JsonSchema\Tests;

use JsonSchema\Validator;

class PhpTypeCastModeTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "a":"c"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"number"}
                  }
                }',
                null,
                array(
                    array(
                        'property' => 'a',
                        'message'  => 'string value found, but a number is required'
                    )
                )
            ),
            array(
                '{
                  "a":"9"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"integer","maximum":"8"}
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
                  "a":"7"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"integer","maximum":8}
                  }
                }',
                Validator::CHECK_MODE_TYPE_CAST
            ),
            array(
                '{
                  "a":1.337
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"number","maximum":8.0}
                  }
                }',
                Validator::CHECK_MODE_TYPE_CAST
            ),
            array(
                '{
                  "a":"9e42"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "a":{"type":"number"}
                  }
                }',
                Validator::CHECK_MODE_TYPE_CAST
            ),
        );
    }
}
