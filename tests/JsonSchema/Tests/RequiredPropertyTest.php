<?php

namespace JsonSchema\Tests;

class RequiredPropertyTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"string","required":true}
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
                  "number": "1.4"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"string","required":true}
                  }
                }'
            ),
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"string"}
                  }
                }'
            ),
    		array(
                '{
				 "number": 0
				}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"integer","required":true}
                  }
                }'
            ),
			array(
                '{
				 "is_active": false
				}',
                '{
                  "type":"object",
                  "properties":{
                    "is_active":{"type":"boolean","required":true}
                  }
                }'
            ),
			array(
                '{
				 "status": null
				}',
                '{
                  "type":"object",
                  "properties":{
                    "status":{"type":"null","required":true}
                  }
                }'
            ),
			array(
                '{
				 "users": []
				}',
                '{
                  "type":"object",
                  "properties":{
                    "users":{"type":"array","required":true}
                  }
                }'
            )
        );
    }
}
