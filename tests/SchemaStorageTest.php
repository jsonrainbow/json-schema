<?php

declare(strict_types=1);

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JsonSchema\Tests;

use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class SchemaStorageTest extends TestCase
{
    public function testResolveRef(): void
    {
        $mainSchema = $this->getMainSchema();
        $mainSchemaPath = 'http://www.example.com/schema.json';

        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve($mainSchemaPath)->willReturn($mainSchema)->shouldBeCalled();

        $schemaStorage = new SchemaStorage($uriRetriever->reveal());

        $this->assertEquals(
            (object) ['type' => 'string'],
            $schemaStorage->resolveRef("$mainSchemaPath#/definitions/house/properties/door")
        );
    }

    public function testResolveTopRef(): void
    {
        $input = json_decode('{"propertyOne":"notANumber"}');
        $schema = json_decode('{"$ref":"#/definition","definition":{"properties":{"propertyOne":{"type":"number"}}}}');
        $v = new Validator();
        $v->validate($input, $schema);
        $this->assertFalse($v->isValid());
    }

    /**
     * @depends testResolveRef
     */
    public function testSchemaWithLocalAndExternalReferencesWithCircularReference(): void
    {
        $mainSchema = $this->getMainSchema();
        $schema2 = $this->getSchema2();
        $schema3 = $this->getSchema3();

        $mainSchemaPath = 'http://www.example.com/schema.json';
        $schema2Path = 'http://www.my-domain.com/schema2.json';
        $schema3Path = 'http://www.my-domain.com/schema3.json';

        /** @var UriRetriever $uriRetriever */
        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve($mainSchemaPath)->willReturn($mainSchema)->shouldBeCalled();
        $uriRetriever->retrieve($schema2Path)->willReturn($schema2)->shouldBeCalled();
        $uriRetriever->retrieve($schema3Path)->willReturn($schema3)->shouldBeCalled();

        $schemaStorage = new SchemaStorage($uriRetriever->reveal());

        // remote ref
        $this->assertEquals(
            $schemaStorage->resolveRef("$schema2Path#/definitions/car"),
            $schemaStorage->resolveRef("$mainSchemaPath#/properties/car")
        );
        $this->assertEquals(
            $schemaStorage->resolveRef("$schema3Path#/wheel"),
            $schemaStorage->resolveRef("$mainSchemaPath#/properties/car/properties/wheel")
        );

        // properties ref
        $this->assertEquals(
            $schemaStorage->resolveRef("$mainSchemaPath#/definitions/yardproperties"),
            $schemaStorage->resolveRef("$mainSchemaPath#/properties/yard/properties")
        );

        // local ref with overriding
        $this->assertEquals(
            $schemaStorage->resolveRef("$mainSchemaPath#/definitions/house/additionalProperties"),
            $schemaStorage->resolveRef("$mainSchemaPath#/properties/house/additionalProperties")
        );
        $this->assertEquals(
            $schemaStorage->resolveRef("$mainSchemaPath#/definitions/house/properties"),
            $schemaStorage->resolveRef("$mainSchemaPath#/properties/house/properties")
        );

        // recursive ref
        $this->assertEquals(
            $schemaStorage->resolveRef("$mainSchemaPath#/definitions/house"),
            $schemaStorage->resolveRef("$mainSchemaPath#/properties/house/properties/house")
        );

        $this->assertEquals(
            $schemaStorage->resolveRef("$mainSchemaPath#/definitions/house"),
            $schemaStorage->resolveRef("$mainSchemaPath#/properties/house/properties/house/properties/house")
        );
    }

    public function testUnresolvableJsonPointExceptionShouldBeThrown(): void
    {
        $this->expectException('JsonSchema\Exception\UnresolvableJsonPointerException');
        $this->expectExceptionMessage('File: http://www.example.com/schema.json is found, but could not resolve fragment: #/definitions/car');

        $mainSchema = $this->getInvalidSchema();
        $mainSchemaPath = 'http://www.example.com/schema.json';

        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve($mainSchemaPath)
            ->willReturn($mainSchema)
            ->shouldBeCalled();

        $schemaStorage = new SchemaStorage($uriRetriever->reveal());
        $schemaStorage->resolveRef("$mainSchemaPath#/definitions/car");
    }

    public function testResolveRefWithNoAssociatedFileName(): void
    {
        $this->expectException('JsonSchema\Exception\UnresolvableJsonPointerException');
        $this->expectExceptionMessage("Could not resolve fragment '#': no file is defined");

        $schemaStorage = new SchemaStorage();
        $schemaStorage->resolveRef('#');
    }

    private function getMainSchema(): object
    {
        return (object) [
            'version' => 'v1',
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'id' => 'http://www.example.com/schema.json',
            'type' => 'object',
            'additionalProperties' => true,
            'required' => [
                'car'
            ],
            'properties' => (object) [
                'car' => (object) [
                    '$ref' => 'http://www.my-domain.com/schema2.json#/definitions/car'
                ],
                'house' => (object) [
                    'additionalProperties' => true,
                    '$ref' => '#/definitions/house'
                ],
                'yard' => (object) [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => (object) [
                        '$ref' => '#/definitions/yardproperties'
                    ]
                ]
            ],
            'definitions' => (object) [
                'house'  => (object) [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'door',
                        'window'
                    ],
                    'properties' => (object) [
                        'door' => (object) [
                            'type' => 'string'
                        ],
                        'window' => (object) [
                            'type' => 'string'
                        ],
                        'house' => (object) [
                            '$ref' => '#/definitions/house'
                        ]
                    ]
                ],
                'yardproperties' => (object) [
                    'tree'=>(object) [
                        'type' => 'string'
                    ],
                    'pool'=>(object) [
                        'type' => 'string'
                    ]
                ]
            ]
        ];
    }

    private function getSchema2(): object
    {
        return (object) [
            'version' => 'v1',
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'id' => 'http://www.my-domain.com/schema2.json',
            'definitions' => (object) [
                'car' => (object) [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => (object) [
                        'id' => (object) [
                            'type' => 'integer'
                        ],
                        'name' => (object) [
                            'type' => 'string',
                            'minLength' => 1
                        ],
                        'wheel' => (object) [
                            '$ref' => './schema3.json#/wheel'
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getSchema3(): object
    {
        return (object) [
            'version' => 'v1',
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'wheel',
            'wheel' => (object) [
                'properties' => (object) [
                    'spokes' => (object) [
                        'type' => 'integer'
                    ],
                    'size' => (object) [
                        'type' => 'integer'
                    ],
                    'car' => (object) [
                        '$ref' => './schema2.json#/definitions/car'
                    ]
                ]
            ]
        ];
    }

    private function getInvalidSchema(): object
    {
        return (object) [
            'version' => 'v1',
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'type' => 'object',
            'properties' => (object) [
                'spokes' => (object) [
                    'type' => 'integer'
                ],
                'size' => (object) [
                    'type' => 'integer'
                ],
                'car' => (object) [
                    '$ref' => '#/definitions/car'
                ]
            ],
            'definitions' => (object) [
                'date' => (object) [
                    'type' => 'string',
                    'pattern' => '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$'
                ]
            ]
        ];
    }

    public function testGetUriRetriever(): void
    {
        $s = new SchemaStorage();
        $s->addSchema('http://json-schema.org/draft-04/schema#');
        $this->assertInstanceOf('\JsonSchema\Uri\UriRetriever', $s->getUriRetriever());
    }

    public function testGetUriResolver(): void
    {
        $s = new SchemaStorage();
        $s->addSchema('http://json-schema.org/draft-04/schema#');
        $this->assertInstanceOf('\JsonSchema\Uri\UriResolver', $s->getUriResolver());
    }

    public function testMetaSchemaFixes(): void
    {
        $s = new SchemaStorage();
        $s->addSchema('http://json-schema.org/draft-03/schema#');
        $s->addSchema('http://json-schema.org/draft-04/schema#');
        $draft_03 = $s->getSchema('http://json-schema.org/draft-03/schema#');
        $draft_04 = $s->getSchema('http://json-schema.org/draft-04/schema#');

        $this->assertEquals('uri-reference', $draft_03->properties->id->format);
        $this->assertEquals('uri-reference', $draft_03->properties->{'$ref'}->format);
        $this->assertEquals('uri-reference', $draft_04->properties->id->format);
    }

    public function testNoDoubleResolve(): void
    {
        $schemaOne = json_decode('{"id": "test/schema", "$ref": "../test2/schema2"}');

        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve('test/schema')->willReturn($schemaOne)->shouldBeCalled();

        $s = new SchemaStorage($uriRetriever->reveal());
        $schema = $s->addSchema('test/schema');

        $r = new \ReflectionObject($s);
        $p = $r->getProperty('schemas');
        $p->setAccessible(true);
        $schemas = $p->getValue($s);

        $this->assertEquals(
            'file://' . getcwd() . '/test2/schema2#',
            $schemas['test/schema']->{'$ref'}
        );
    }
}
