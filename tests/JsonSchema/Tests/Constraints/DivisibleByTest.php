<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class DivisibleByTest extends BaseTestCase
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
                    "value":{"type":"number","divisibleBy":3}
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
                  "value":6
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"number","divisibleBy":3}
                  }
                }'
            )
        );
    }
}
