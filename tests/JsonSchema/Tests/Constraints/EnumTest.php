<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class EnumTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":"Morango"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"string",
                      "enum":["Abacate","Manga","Pitanga"],
                      "required":true
                    }
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
                  "value":"Abacate"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
                  },
                  "additionalProperties":false
                }'
            ),
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"string",
                      "enum":["Abacate","Manga","Pitanga"],
                      "required":false
                    }
                  },
                  "additionalProperties":false
                }'
            )
        );
    }
}
