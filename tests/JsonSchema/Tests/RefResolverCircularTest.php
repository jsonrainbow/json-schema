<?php

namespace JsonSchema\Tests;

use JsonSchema\RefResolver;
use JsonSchema\Uri\Retrievers\PredefinedArray;
use JsonSchema\Uri\UriRetriever;
use PHPUnit_Framework_TestCase;

/**
 * Test RefResolver with Circular references
 */
class RefResolverCircularTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestCases
     */
    public function testResolve($schemas, $targetUri, $expectedJson)
    {
        $expectedSchema = json_decode($expectedJson);
        if (!$expectedSchema) {
            $this->fail('Invalid expected JSON: ' . json_last_error_msg());
        }

        $uriRetriever = new UriRetriever();
        $retriever = new PredefinedArray($schemas);
        $uriRetriever->setUriRetriever($retriever);
        $refResolver = new RefResolver($uriRetriever);

        $schema = $uriRetriever->retrieve($targetUri);
        $refResolver->resolve($schema, $targetUri);

        $this->assertEqualSchema($expectedSchema, $schema);
    }

    private function assertEqualSchema($expectedSchema, $actualSchema)
    {
        $path = '/';
        if (isset($actualSchema->id)){
            $path = $actualSchema->id;
        }
        $this->assertEqualObject($expectedSchema, $actualSchema, $path);
    }

    private function assertEqualObject($expectedObject, $actualObject, $path)
    {
        if (!is_object($actualObject)) {
            $actualObject = var_export($actualObject, true);
            $this->fail("No object found at $path, instead found $actualObject");
        }

        foreach ($expectedObject as $key => $expectedValue) {
            if (!property_exists($actualObject, $key)) {
                $this->fail(sprintf("Key %s did not exist at path %s", $key, $path));
            }
            $actualValue = $actualObject->$key;

            $subpath = $path . '->' . $key;
            $this->assertEqualValue($expectedValue, $actualValue, $subpath);
        }
    }

    private function assertEqualArray($expectedArray, $actualArray, $path)
    {
        if (!is_array($actualArray)) {
            $actualArray = var_export($actualArray, true);
            $this->fail("No array found at $path, instead found $actualArray");
        }

        foreach ($expectedArray as $key => $expectedValue) {
            if (!array_key_exists($key, $expectedArray)) {
                $this->fail(sprintf("Index %s did not exist in array at %s", $key, $path));
            }
            $actualValue = $actualArray[$key];

            $subpath = $path . '[' . $key . ']';
            $this->assertEqualValue($expectedValue, $actualValue, $subpath);
        }
    }

    private function assertEqualValue($expectedValue, $actualValue, $path)
    {
        if (is_object($expectedValue)) {
            $this->assertEqualObject($expectedValue, $actualValue, $path);
            return;
        }

        if (is_array($expectedValue)) {
            $this->assertEqualArray($expectedValue, $actualValue, $path);
            return;
        }

        if ('NULL' == $expectedValue) {
            $this->assertNull($actualValue, "NULL value not found at $path");
            return;
        }

        $this->assertEquals($expectedValue, $actualValue, "Wrong value at path $path");
    }


    public function getTestCases()
    {
        return [
            // Test case 0, recursive self reference
            [
                [
                    '/schema1.json' => '{
                        "type":"object",
                        "properties": {
                            "number":42,
                            "myself":{
                                "$ref":"#"
                            }
                        }
                    }'
                ],
                '/schema1.json',
                '{
                    "type":"object",
                    "properties": {
                        "number":42,
                        "myself": {
                            "type":"object",
                            "properties": {
                                "number":42,
                                "myself":{
                                    "properties": {
                                        "number":42,
                                        "myself": {}
                                    }
                                }
                            }
                        }
                    }
                }'
            ],
            // Test case 1, circular reference between 2 schemas
            [
                [
                    '/schema1.json' => '{
                        "type":"object",
                        "properties": {
                            "number":42,
                            "other":{
                                "$ref":"/schema2.json#"
                            }
                        }
                    }',
                    '/schema2.json' => '{
                        "type":"object",
                        "properties": {
                            "prop1":"hipp",
                            "schema1":{
                                "$ref":"/schema1.json#"
                            }
                        }
                    }'
                ],
                '/schema1.json',
                '{
                     "type":"object",
                     "properties": {
                         "number":42,
                         "other": {
                             "type":"object",
                             "properties": {
                                 "prop1":"hipp",
                                 "schema1":{
                                     "properties": {
                                         "number": 42,
                                         "other": {
                                             "properties": {
                                                 "prop1":"hipp",
                                                 "schema1": {
                                                     "properties": {
                                                         "number": 42
                                                     }
                                                 }
                                             }
                                         }
                                     }
                                 }
                             }
                         }
                     }
                }'
            ],
            // Test case 2, reference to a self reference
            [
                [
                    '/schema1.json' => '{
                        "type":"object",
                        "definitions": {
                            "tree": {
                                "$ref": "/tree.json#"
                            }
                        },
                        "properties": {
                            "number":42,
                            "node":{
                                "something":"here"
                            }
                        }
                    }',
                    '/tree.json' => '{
                        "type":"object",
                        "properties": {
                            "content":"string",
                            "child":{
                                "$ref":"/tree.json#"
                            }
                        }
                    }'
                ],
                '/schema1.json',
                '{
                    "type":"object",
                    "definitions": {
                        "tree": {
                            "type":"object",
                            "properties": {
                                "content":"string",
                                "child": {
                                    "type":"object",
                                    "properties": {
                                        "content":"string",
                                        "child": {
                                            "type":"object",
                                            "properties": {
                                                "content":"string",
                                                "child": {
                                                    "type": "object"
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "properties": {
                        "number":42,
                        "node":{
                            "something": "here"
                        }
                    }
                }'
            ],
            // Test case 3, indirect reference to tree.json
            [
                [
                    '/schema1.json' => '{
                        "type":"object",
                        "definitions": {
                            "tree": {
                                "$ref": "/tree.json#"
                            }
                        },
                        "properties": {
                            "number":42,
                            "node":{
                                "$ref":"#/definitions/tree"
                            }
                        }
                    }',
                    '/tree.json' => '{
                        "type":"object",
                        "properties": {
                            "content":"string",
                            "child":{
                                "some":"data"
                            }
                        }
                    }'
                ],
                '/schema1.json',
                '{
                    "type":"object",
                    "definitions": {
                        "tree": {
                            "type":"object",
                            "properties": {
                                "content":"string",
                                "child": {
                                    "some":"data"
                                }
                            }
                        }
                    },
                    "properties": {
                        "number":42,
                        "node":{
                            "type":"object",
                            "properties": {
                                "content":"string",
                                "child": {
                                    "some":"data"
                                }
                            }
                        }
                    }
                }'
            ],
            // Test case 4, indirect reference to self referencing tree.json
            [
                [
                    '/schema1.json' => '{
                        "type":"object",
                        "definitions": {
                            "tree": {
                                "$ref": "/tree.json#"
                            }
                        },
                        "properties": {
                            "number":42,
                            "node":{
                                "$ref":"#/definitions/tree"
                            }
                        }
                    }',
                    '/tree.json' => '{
                        "type":"object",
                        "properties": {
                            "content":"string",
                            "child":{
                                "$ref":"/tree.json#"
                            }
                        }
                    }'
                ],
                '/schema1.json',
                '{
                    "type":"object",
                    "definitions": {
                        "tree": {
                            "type":"object",
                            "properties": {
                                "content":"string",
                                "child": {
                                    "type":"object",
                                    "properties": {
                                        "content":"string",
                                        "child": {
                                            "type":"object",
                                            "properties": {
                                                "content":"string",
                                                "child": {
                                                    "type": "object"
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "properties": {
                        "number":42,
                        "node":{
                            "type":"object",
                            "properties": {
                                "content":"string",
                                "child": {
                                    "type":"object",
                                    "properties": {
                                        "content":"string",
                                        "child": {
                                            "type":"object",
                                            "properties": {
                                                "content":"string",
                                                "child": {
                                                    "type": "object"
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }'
            ],
            // Test case 5, reference spanning another reference
            [
                [
                    '/schema1.json' => '{
                        "type":"object",
                        "definitions": {
                            "zee": {
                                "$ref": "/schema2.json#/definitions/foo/bar"
                            }
                        },
                        "properties": {
                            "number":42,
                            "node":{
                                "$ref":"#/definitions/zee"
                            }
                        }
                    }',
                    '/schema2.json' => '{
                        "type":"object",
                        "definitions": {
                            "foo":{
                                "$ref":"/schema3.json#/definitions/zoopla"
                            }
                        }
                    }',
                    '/schema3.json' => '{
                        "type":"object",
                        "definitions": {
                            "zoopla":{
                                "bar": {
                                    "this" : "Reffered to value"
                                }
                            }
                        }
                    }'
                ],
                '/schema1.json',
                '{
                    "type": "object",
                    "definitions": {
                        "zee": {
                            "this" : "Reffered to value"
                        }
                    },
                    "properties": {
                        "number": 42,
                        "node": {
                            "this" : "Reffered to value"
                        }
                    }
                }'
            ],
            // Test case 6, reference spanning references back to same document
            [
                [
                    '/schema1.json' => '{
                        "type":"object",
                        "definitions": {
                            "zee": {
                                "$ref": "/schema2.json#/definitions/foo/bar"
                            },
                            "zoo": {
                                "$ref": "/schema2.json#/definitions/norro"
                            }
                        },
                        "properties": {
                            "number":42,
                            "node":{
                                "$ref":"#/definitions/zee"
                            }
                        }
                    }',
                    '/schema2.json' => '{
                        "type":"object",
                        "definitions": {
                            "foo":{
                                "$ref":"/schema3.json#/definitions/zoopla"
                            },
                            "norro": {
                                "peddle": {
                                     "this" : "Reffered to value"
                                }
                            }
                        }
                    }',
                    '/schema3.json' => '{
                        "type":"object",
                        "definitions": {
                            "zoopla":{
                                "bar": {
                                    "$ref": "/schema1.json#/definitions/zoo/peddle"
                                }
                            }
                        }
                    }'
                ],
                '/schema1.json',
                '{
                    "type": "object",
                    "definitions": {
                        "zee": {
                            "this" : "Reffered to value"
                        },
                        "zoo": {
                            "peddle": {
                                "this" : "Reffered to value"
                            }
                        }
                    },
                    "properties": {
                        "number": 42,
                        "node": {
                            "this" : "Reffered to value"
                        }
                    }
                }'
            ]
        ];
    }

    /**
     * @dataProvider getImpossibleTestCase
     */
    public function testImpossibleResolve($schemas, $targetUri)
    {
        $uriRetriever = new UriRetriever();
        $retriever = new PredefinedArray($schemas);
        $uriRetriever->setUriRetriever($retriever);
        $refResolver = new RefResolver($uriRetriever);

        $schema = $uriRetriever->retrieve($targetUri);

        $this->setExpectedException('JsonSchema\Exception\ResourceNotFoundException');
        $refResolver->resolve($schema, $targetUri);
    }

    public function getImpossibleTestCase()
    {
        return [
            // Test case 0
            [
                [
                    '/schema1.json' => '{
                        "type": "object",
                        "definitions": {
                            "humbug": {
                                "$ref": "/schema2.json#/definitions/foo/bar/baz"
                            }
                        }
                    }',
                    '/schema2.json' => '{
                        "type": "object",
                        "definitions": {
                            "foo": {
                                "$ref": "/schema1.json#/definitions/humbug/goo/daa"
                            }
                        }
                    }'
                ],
                '/schema1.json'
            ]
        ];
    }
}
