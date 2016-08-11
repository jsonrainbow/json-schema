<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;
use Prophecy\Argument;

/**
 * @package JsonSchema\Tests
 * @author Joost Nijhuis <jnijhuis81@gmail.com>
 * @author Rik Jansen <rikjansen@gmail.com>
 * @group RefResolver
 */
class RefResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var RefResolver */
    private $refResolver;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->refResolver = new RefResolver(new UriRetriever(), new UriResolver());
    }

    public function testSchemaWithLocalAndExternalReferencesWithCircularReference()
    {
        $mainSchema = $this->getMainSchema();
        $schema2 = $this->getSchema2();
        $schema3 = $this->getSchema3();

        /** @var UriRetriever $uriRetriever */
        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve('http://www.example.com/schema.json')
            ->willReturn($mainSchema)
            ->shouldBeCalled($mainSchema);
        $uriRetriever->retrieve('http://www.my-domain.com/schema2.json')
            ->willReturn($schema2)
            ->shouldBeCalled();
        $uriRetriever->retrieve('http://www.my-domain.com/schema3.json')
            ->willReturn($schema3)
            ->shouldBeCalled();

        $refResolver = new RefResolver($uriRetriever->reveal(), new UriResolver());
        $refResolver->resolve('http://www.example.com/schema.json');

        // ref schema merged into schema
        $this->assertSame($schema2->definitions->car->type, $mainSchema->properties->car->type);
        $this->assertSame(
            $schema2->definitions->car->additionalProperties,
            $mainSchema->properties->car->additionalProperties
        );
        $this->assertSame($schema2->definitions->car->properties, $mainSchema->properties->car->properties);
        $this->assertFalse(property_exists($mainSchema->properties->car, '$ref'));

        // ref schema combined with current schema
        $this->assertFalse(property_exists($mainSchema->properties->house, '$ref'));
        $this->assertSame(true, $mainSchema->properties->house->allOf[0]->additionalProperties);
        $this->assertSame($mainSchema->definitions->house, $mainSchema->properties->house->allOf[1]);

        $this->assertNotSame($mainSchema->definitions->house, $mainSchema->definitions->house->properties->house);
        $this->assertNotSame(
            $mainSchema->definitions->house,
            $mainSchema->definitions->house->properties->house->properties->house
        );
        $this->assertSame(
            $mainSchema->definitions->house->properties->house,
            $mainSchema->definitions->house->properties->house->properties->house->properties->house
        );
        $this->assertSame(
            $mainSchema->definitions->house->properties->house,
            $mainSchema->definitions->house->properties->house->properties->house->properties->house->properties->house
        );

        $this->assertNotSame($schema3->wheel, $mainSchema->properties->car->properties->wheel);
        $this->assertSame(
            $schema3->wheel->properties->spokes,
            $mainSchema->properties->car->properties->wheel->properties->spokes
        );

        $this->assertNotSame($schema3->wheel->properties->car, $mainSchema->properties->car);
        $this->assertSame($schema3->wheel->properties->car->properties, $mainSchema->properties->car->properties);
    }

    function testUnresolvableJsonPointExceptionShouldBeThrown()
    {
        $this->setExpectedException(
            'JsonSchema\Exception\UnresolvableJsonPointerException',
            'File: http://www.example.com/schema.json is found, but could not resolve fragment: #/definitions/car'
        );

        $mainSchema = $this->getInvalidSchema();

        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve('http://www.example.com/schema.json')
            ->willReturn($mainSchema)
            ->shouldBeCalled($mainSchema);

        $refResolver = new RefResolver($uriRetriever->reveal(), new UriResolver());
        $refResolver->resolve('http://www.example.com/schema.json');
    }

    public function testExternalReferencesLoadedOnlyOnce()
    {
        $mainSchema = $this->getMainSchema();
        $schema2 = $this->getSchema2();
        $schema3 = $this->getSchema3();

        /** @var UriRetriever $uriRetriever */
        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve('http://www.example.com/schema.json')
            ->willReturn($mainSchema)
            ->shouldBeCalledTimes(1);
        $uriRetriever->retrieve('http://www.my-domain.com/schema2.json')
            ->willReturn($schema2)
            ->shouldBeCalledTimes(1);
        $uriRetriever->retrieve('http://www.my-domain.com/schema3.json')
            ->willReturn($schema3)
            ->shouldBeCalledTimes(1);

        $refResolver = new RefResolver($uriRetriever->reveal(), new UriResolver());
        $refResolver->resolve('http://www.example.com/schema.json');
        $refResolver->resolve('http://www.example.com/schema.json');
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
}
