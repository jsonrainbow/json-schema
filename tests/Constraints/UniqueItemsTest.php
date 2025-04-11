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

    public function getInvalidTests(): array
    {
        return [
            'Non unique integers' => [
                'input' => '[1,2,2]',
                'schema' => '{
                  "type":"array",
                  "uniqueItems": true
                }'
            ],
            'Non unique objects' => [
                'input' => '[{"a":"b"},{"a":"c"},{"a":"b"}]',
                'schema' => '{
                  "type":"array",
                  "uniqueItems": true
                }'
            ],
            'Non unique objects - three levels deep' => [
                'input' => '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : true}}}]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'Non unique mathematical values for the number one' => [
                'input' => '[1.0, 1.00, 1]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'Non unique arrays' => [
                'input' => '[["foo"], ["foo"]]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'Non unique mix of different types' => [
                'input' => '[{}, [1], true, null, {}, 1]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'objects are non-unique despite key order' => [
                'input' => '[{"a": 1, "b": 2}, {"b": 2, "a": 1}]',
                'schema' => '{"uniqueItems": true}',
            ]
        ];
    }

    public function getValidTests(): array
    {
        return [
            'unique integers' => [
                'input' => '[1,2,3]',
                'schema' => '{
                    "type":"array",
                    "uniqueItems": true
                }'
            ],
            'unique objects' =>[
                'input' => '[{"foo": 12}, {"bar": false}]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'Integer one and boolean true' => [
                'input' => '[1, true]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'Integer zero and boolean false' => [
                'input' => '[0, false]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'Objects with different value three levels deep' => [
                'input' => '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : false}}}]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'Array of strings' => [
                'input' => '[["foo"], ["bar"]]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            'Object, Array, boolean, null and integer' => [
                'input' => '[{}, [1], true, null, 1]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": true
                }'
            ],
            // below equals the invalid tests, but with uniqueItems set to false
            'Non unique integers' => [
                'input' => '[1,2,2]',
                'schema' =>  '{
                  "type":"array",
                  "uniqueItems": false
                }'
            ],
            'Non unique objects' => [
                'input' => '[{"a":"b"},{"a":"c"},{"a":"b"}]',
                'schema' => '{
                  "type":"array",
                  "uniqueItems": false
                }'
            ],
            'Non unique objects - three levels deep' => [
                'input' => '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : true}}}]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": false
                }'
            ],
            'Non unique mathematical values for the number one' => [
                'input' => '[1.0, 1.00, 1]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": false
                }'
            ],
            'Non unique arrays' => [
                'input' => '[["foo"], ["foo"]]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": false
                }'
            ],
            'Non unique mix of different types' => [
                'input' => '[{}, [1], true, null, {}, 1]',
                'schema' => '{
                    "type": "array",
                    "uniqueItems": false
                }'
            ]
        ];
    }
}
