<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class UniqueItemsTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests()
    {
        return [
            [
                '[1,2,2]',
                '{
                  "type":"array",
                  "uniqueItems": true
                }'
            ],
            [
                '[{"a":"b"},{"a":"c"},{"a":"b"}]',
                '{
                  "type":"array",
                  "uniqueItems": true
                }'
            ],
            [
                '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : true}}}]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            [
                '[1.0, 1.00, 1]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            [
                '[["foo"], ["foo"]]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            [
                '[{}, [1], true, null, {}, 1]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ]
        ];
    }

    public function getValidTests()
    {
        return [
            [
                '[1,2,3]',
                '{
                  "type":"array",
                  "uniqueItems": true
                }'
            ],
            [
                '[{"foo": 12}, {"bar": false}]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            [
                '[1, true]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            [
                '[0, false]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            [
                '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : false}}}]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            [
                '[["foo"], ["bar"]]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            [
                '[{}, [1], true, null, 1]',
                '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            // below equals the invalid tests, but with uniqueItems set to false
            [
                '[1,2,2]',
                '{
                  "type":"array",
                  "uniqueItems": false
                }'
            ],
            [
                '[{"a":"b"},{"a":"c"},{"a":"b"}]',
                '{
                  "type":"array",
                  "uniqueItems": false
                }'
            ],
            [
                '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : true}}}]',
                '{
                    "type": "array",
                    "uniqueItems": false
                }'
            ],
            [
                '[1.0, 1.00, 1]',
                '{
                    "type": "array",
                    "uniqueItems": false
                }'
            ],
            [
                '[["foo"], ["foo"]]',
                '{
                    "type": "array",
                    "uniqueItems": false
                }'
            ],
            [
                '[{}, [1], true, null, {}, 1]',
                '{
                    "type": "array",
                    "uniqueItems": false
                }'
            ]
        ];
    }
}
