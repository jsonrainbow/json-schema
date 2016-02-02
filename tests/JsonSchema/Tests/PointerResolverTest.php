<?php

namespace JsonSchema;

use JsonSchema\PointerResolver;

class PointerResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testCanRetrieveRootPointer()
    {
        $json = json_decode('{ "data": [ "a", "b", "c" ] }');
        $resolver = new PointerResolver();
        $this->assertSame($json, $resolver->resolvePointer($json, ''));
    }

    public function testCanRetrieveArrayElement()
    {
        $json = json_decode('[ "a", "b", "c" ]');
        $resolver = new PointerResolver();
        $this->assertEquals('c', $resolver->resolvePointer($json, '/2'));
    }

    public function testCanRetrieveArrayElementInsideObject()
    {
        $json = json_decode('{ "data": [ "a", "b", "c" ] }');
        $resolver = new PointerResolver();
        $this->assertEquals('b', $resolver->resolvePointer($json, '/data/1'));
    }

    public function testCanRetrieveDeepArrayReference()
    {
        $json = json_decode('[ { "a": 2 }, "b", "c" ]');
        $resolver = new PointerResolver();
        $this->assertEquals(2, $resolver->resolvePointer($json, '/0/a'));
    }

    public function testCanRetrieveLastArrayElement()
    {
        $json = json_decode('{ "data": [ "a", "b", "c" ] }');
        $resolver = new PointerResolver();
        $this->assertEquals('c', $resolver->resolvePointer($json, '/data/-'));
    }

    public function testCanRetrieveNull()
    {
        $json = json_decode('{ "a": { "b": null } }');
        $resolver = new PointerResolver();
        $this->assertNull($resolver->resolvePointer($json, '/a/b'));
    }

    public function testCanRetrieveKeyWithSlash()
    {
        $json = json_decode('{ "a/b.txt": 123 }');
        $resolver = new PointerResolver();
        $this->assertEquals(123, $resolver->resolvePointer($json, '/a%2Fb.txt'));
    }

    public function testCanRetrieveViaEscapedSequences()
    {
        $json = json_decode('{"a/b/c": 1, "m~n": 8, "a": {"b": {"c": 12} } }');
        $resolver = new PointerResolver();

        $this->assertEquals(1, $resolver->resolvePointer($json, '/a~1b~1c'));
        $this->assertEquals(8, $resolver->resolvePointer($json, '/m~0n'));
        $this->assertEquals(12, $resolver->resolvePointer($json, '/a/b/c'));
    }

    /**
     * @dataProvider specialCasesProvider
     */
    public function testCanEvaluateSpecialCases($expected, $pointerValue)
    {
        $json = json_decode('{"foo":["bar","baz"],"":0,"a/b":1,"c%d":2,"e^f":3,"g|h":4,"k\"l":6," ":7,"m~n":8}');
        $resolver = new PointerResolver();

        $this->assertEquals($expected, $resolver->resolvePointer($json, $pointerValue));
    }

    /**
     * @expectedException JsonSchema\Exception\InvalidPointerException
     * @dataProvider      invalidPointersProvider
     */
    public function testInvalidPointersThrowsInvalidPointerException($pointerValue)
    {
        $json = json_decode('{ "a": 1 }');
        $resolver = new PointerResolver();
        $resolver->resolvePointer($json, $pointerValue);
    }

    /**
     * @expectedException JsonSchema\Exception\ResourceNotFoundException
     * @dataProvider      nonExistantPointersProvider
     */
    public function testFailureToResolvePointerThrowsResourceNotFoundException($jsonString, $pointerValue)
    {
        $json = json_decode($jsonString);
        $resolver = new PointerResolver();
        $resolver->resolvePointer($json, $pointerValue);
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
