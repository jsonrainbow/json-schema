<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class NumberAndIntegerTypesTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "number": 1.4
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"integer"}
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
                  "number": 1
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            ),
            array(
                '{
                  "number": 1.4
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            ),
            array(
                '{
                  "number": "1.4"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            )
        );
    }
}
