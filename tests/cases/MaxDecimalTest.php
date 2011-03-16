<?php

class MaxDecimalTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":5.6333
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"number","maxDecimal":3}
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
                  "value":5.633
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"number","maxDecimal":3}
                  }
                }'
            )
        );
    }
}
