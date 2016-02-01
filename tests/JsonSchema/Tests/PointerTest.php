<?php

namespace JsonSchema;

use JsonSchema\Pointer;

class PointerTest extends \PHPUnit_Framework_TestCase
{
    public function testCanRetrieveRootPointer()
    {
        $json = json_decode('{ "data": [ "a", "b", "c" ] }');
        $pointer = new Pointer($json);
        $this->assertSame($json, $pointer->get(''));
    }

    public function testCanRetrieveArrayElement()
    {
        $json = json_decode('[ "a", "b", "c" ]');
        $pointer = new Pointer($json);
        $this->assertEquals('c', $pointer->get('/2'));
    }

    public function testCanRetrieveArrayElementInsideObject()
    {
        $json = json_decode('{ "data": [ "a", "b", "c" ] }');
        $pointer = new Pointer($json);
        $this->assertEquals('b', $pointer->get('/data/1'));
    }

    public function testCanRetrieveDeepArrayReference()
    {
        $json = json_decode('[ { "a": 2 }, "b", "c" ]');
        $pointer = new Pointer($json);
        $this->assertEquals(2, $pointer->get('/0/a'));
    }

    public function testCanRetrieveLastArrayElement()
    {
        $json = json_decode('{ "data": [ "a", "b", "c" ] }');
        $pointer = new Pointer($json);
        $this->assertEquals('c', $pointer->get('/data/-'));
    }

    public function testCanRetrieveNull()
    {
        $json = json_decode('{ "a": { "b": null } }');
        $pointer = new Pointer($json);
        $this->assertNull($pointer->get('/a/b'));
    }

    public function testCanRetrieveKeyWithSlash()
    {
        $json = json_decode('{ "a/b.txt": 123 }');
        $pointer = new Pointer($json);
        $this->assertEquals(123, $pointer->get('/a%2Fb.txt'));
    }

    public function testCanRetrieveViaEscapedSequences()
    {
        $json = json_decode('{"a/b/c": 1, "m~n": 8, "a": {"b": {"c": 12} } }');
        $pointer = new Pointer($json);

        $this->assertEquals(1, $pointer->get('/a~1b~1c'));
        $this->assertEquals(8, $pointer->get('/m~0n'));
        $this->assertEquals(12, $pointer->get('/a/b/c'));
    }

    /**
     * @dataProvider specialCasesProvider
     */
    public function testCanEvaluateSpecialCases($expected, $pointerValue)
    {
        $json = json_decode('{"foo":["bar","baz"],"":0,"a/b":1,"c%d":2,"e^f":3,"g|h":4,"k\"l":6," ":7,"m~n":8}');
        $pointer = new Pointer($json);

        $this->assertEquals($expected, $pointer->get($pointerValue));
    }

    /**
     * @expectedException JsonSchema\Exception\InvalidPointerException
     * @dataProvider      invalidPointersProvider
     */
    public function testInvalidPointersThrowsInvalidPointerException($pointerValue)
    {
        $pointer = new Pointer(json_decode('{ "a": 1 }'));
        $pointer->get($pointerValue);
    }

    /**
     * @expectedException JsonSchema\Exception\ResourceNotFoundException
     * @dataProvider      nonExistantPointersProvider
     */
    public function testFailureToResolvePointerThrowsResourceNotFoundException($jsonString, $pointerValue)
    {
        $pointer = new Pointer(json_decode($jsonString));
        $pointer->get($pointerValue);
    }

    public function specialCasesProvider()
    {
        return array(
          array(json_decode('{"foo":["bar","baz"],"":0,"a\/b":1,"c%d":2,"e^f":3,"g|h":4,"k\"l":6," ":7,"m~n":8}'), ''),
          array(array('bar', 'baz'), '/foo'),
          array('bar', '/foo/0'),
          array(0, '/'),
          array(1, '/a~1b'),
          array(2, '/c%d'),
          array(3, '/e^f'),
          array(4, '/g|h'),
          array(6, "/k\"l"),
          array(7, '/ '),
          array(8, '/m~0n'),
        );
    }

    public function invalidPointersProvider()
    {
        return array(
            // Invalid starting characters
            array('*'),
            array('#'),

            // Invalid data types
            array(array()),
            array(15),
            array(null),
        );
    }

    public function nonExistantPointersProvider()
    {
        return array(
            array('[ "a", "b", "c" ]', '/3'),
            array('{ "data": { "a": {"b": "c"} } }', '/data/b'),
        );
    }
}
