<?php

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
    public function testResolveRef()
    {
        $mainSchema = $this->getMainSchema();
        $mainSchemaPath = 'http://www.example.com/schema.json';

        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve($mainSchemaPath)->willReturn($mainSchema)->shouldBeCalled();

        $schemaStorage = new SchemaStorage($uriRetriever->reveal());

        $this->assertEquals(
            (object) array('type' => 'string'),
            $schemaStorage->resolveRef("$mainSchemaPath#/definitions/house/properties/door")
        );
    }

    public function testResolveTopRef()
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
    public function testSchemaWithLocalAndExternalReferencesWithCircularReference()
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
        $this->assertNotEquals(
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

    public function testUnresolvableJsonPointExceptionShouldBeThrown()
    {
        $this->setExpectedException(
            'JsonSchema\Exception\UnresolvableJsonPointerException',
            'File: http://www.example.com/schema.json is found, but could not resolve fragment: #/definitions/car'
        );

        $mainSchema = $this->getInvalidSchema();
        $mainSchemaPath = 'http://www.example.com/schema.json';

        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve($mainSchemaPath)
            ->willReturn($mainSchema)
            ->shouldBeCalled();

        $schemaStorage = new SchemaStorage($uriRetriever->reveal());
        $schemaStorage->resolveRef("$mainSchemaPath#/definitions/car");
    }

    public function testResolveRefWithNoAssociatedFileName()
    {
        $this->setExpectedException(
            'JsonSchema\Exception\UnresolvableJsonPointerException',
            "Could not resolve fragment '#': no file is defined"
        );

        $schemaStorage = new SchemaStorage();
        $schemaStorage->resolveRef('#');
    }

    /**
     * @return object
     */
    private function getMainSchema()
    {
        return (object) array(
            'version' => 'v1',
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'id' => 'http://www.example.com/schema.json',
            'type' => 'object',
            'additionalProperties' => true,
            'required' => array(
                'car'
            ),
            'properties' => (object) array(
                'car' => (object) array(
                    '$ref' => 'http://www.my-domain.com/schema2.json#/definitions/car'
                ),
                'house' => (object) array(
                    'additionalProperties' => true,
                    '$ref' => '#/definitions/house'
                ),
                'yard' => (object) array(
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => (object) array(
                        '$ref' => '#/definitions/yardproperties'
                    )
                )
            ),
            'definitions' => (object) array(
                'house'  => (object) array(
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => array(
                        'door',
                        'window'
                    ),
                    'properties' => (object) array(
                        'door' => (object) array(
                            'type' => 'string'
                        ),
                        'window' => (object) array(
                            'type' => 'string'
                        ),
                        'house' => (object) array(
                            '$ref' => '#/definitions/house'
                        )
                    )
                ),
                'yardproperties' => (object) array(
                    'tree'=>(object) array(
                        'type' => 'string'
                    ),
                    'pool'=>(object) array(
                        'type' => 'string'
                    )
                )
            )
        );
    }

    /**
     * @return object
     */
    private function getSchema2()
    {
        return (object) array(
            'version' => 'v1',
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'id' => 'http://www.my-domain.com/schema2.json',
            'definitions' => (object) array(
                'car' => (object) array(
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => (object) array(
                        'id' => (object) array(
                            'type' => 'integer'
                        ),
                        'name' => (object) array(
                            'type' => 'string',
                            'minLength' => 1
                        ),
                        'wheel' => (object) array(
                            '$ref' => './schema3.json#/wheel'
                        )
                    )
                )
            )
        );
    }

    /**
     * @return object
     */
    private function getSchema3()
    {
        return (object) array(
            'version' => 'v1',
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'wheel',
            'wheel' => (object) array(
                'properties' => (object) array(
                    'spokes' => (object) array(
                        'type' => 'integer'
                    ),
                    'size' => (object) array(
                        'type' => 'integer'
                    ),
                    'car' => (object) array(
                        '$ref' => './schema2.json#/definitions/car'
                    )
                )
            )
        );
    }

    /**
     * @return object
     */
    private function getInvalidSchema()
    {
        return (object) array(
            'version' => 'v1',
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'type' => 'object',
            'properties' => (object) array(
                'spokes' => (object) array(
                    'type' => 'integer'
                ),
                'size' => (object) array(
                    'type' => 'integer'
                ),
                'car' => (object) array(
                    '$ref' => '#/definitions/car'
                )
            ),
            'definitions' => (object) array(
                'date' => (object) array(
                    'type' => 'string',
                    'pattern' => '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$'
                )
            )
        );
    }

    public function testGetUriRetriever()
    {
        $s = new SchemaStorage();
        $s->addSchema('http://json-schema.org/draft-04/schema#');
        $this->assertInstanceOf('\JsonSchema\Uri\UriRetriever', $s->getUriRetriever());
    }

    public function testGetUriResolver()
    {
        $s = new SchemaStorage();
        $s->addSchema('http://json-schema.org/draft-04/schema#');
        $this->assertInstanceOf('\JsonSchema\Uri\UriResolver', $s->getUriResolver());
    }

    public function testMetaSchemaFixes()
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

    public function testNoDoubleResolve()
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
