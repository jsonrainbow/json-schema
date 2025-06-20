<?php

namespace JsonSchema\Tests\Constraints;

class UniqueItemsTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
{
        yield 'Non unique integers' => [
            'input' => '[1,2,2]',
            'schema' => '{
              "type":"array",
              "uniqueItems": true
            }'
        ];
        yield 'Non unique objects' => [
            'input' => '[{"a":"b"},{"a":"c"},{"a":"b"}]',
            'schema' => '{
              "type":"array",
              "uniqueItems": true
            }'
        ];
        yield 'Non unique objects - three levels deep' => [
            'input' => '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : true}}}]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'Non unique mathematical values for the number one' => [
            'input' => '[1.0, 1.00, 1]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'Non unique arrays' => [
            'input' => '[["foo"], ["foo"]]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'Non unique mix of different types' => [
            'input' => '[{}, [1], true, null, {}, 1]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'objects are non-unique despite key order' => [
            'input' => '[{"a": 1, "b": 2}, {"b": 2, "a": 1}]',
            'schema' => '{"uniqueItems": true}',
        ];
    }

    public function getValidTests(): \Generator
    {
        yield 'unique integers' => [
            'input' => '[1,2,3]',
            'schema' => '{
                "type":"array",
                "uniqueItems": true
            }'
        ];
        yield 'unique objects' =>[
            'input' => '[{"foo": 12}, {"bar": false}]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'Integer one and boolean true' => [
            'input' => '[1, true]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'Integer zero and boolean false' => [
            'input' => '[0, false]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'Objects with different value three levels deep' => [
            'input' => '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : false}}}]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'Array of strings' => [
            'input' => '[["foo"], ["bar"]]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        yield 'Object, Array, boolean, null and integer' => [
            'input' => '[{}, [1], true, null, 1]',
            'schema' => '{
                "type": "array",
                "uniqueItems": true
            }'
        ];
        // below equals the invalid tests, but with uniqueItems set to false
        yield 'Non unique integers' => [
            'input' => '[1,2,2]',
            'schema' =>  '{
              "type":"array",
              "uniqueItems": false
            }'
        ];
        yield 'Non unique objects' => [
            'input' => '[{"a":"b"},{"a":"c"},{"a":"b"}]',
            'schema' => '{
              "type":"array",
              "uniqueItems": false
            }'
        ];
        yield 'Non unique objects - three levels deep' => [
            'input' => '[{"foo": {"bar" : {"baz" : true}}}, {"foo": {"bar" : {"baz" : true}}}]',
            'schema' => '{
                "type": "array",
                "uniqueItems": false
            }'
        ];
        yield 'Non unique mathematical values for the number one' => [
            'input' => '[1.0, 1.00, 1]',
            'schema' => '{
                "type": "array",
                "uniqueItems": false
            }'
        ];
        yield 'Non unique arrays' => [
            'input' => '[["foo"], ["foo"]]',
            'schema' => '{
                "type": "array",
                "uniqueItems": false
            }'
        ];
        yield 'Non unique mix of different types' => [
            'input' => '[{}, [1], true, null, {}, 1]',
            'schema' => '{
                "type": "array",
                "uniqueItems": false
            }'
        ];
    }
}
