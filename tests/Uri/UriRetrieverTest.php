<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Uri;

use JsonSchema\Exception\JsonDecodingException;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @group UriRetriever
 */
class UriRetrieverTest extends TestCase
{
    protected $validator;

    protected function setUp()
    {
        $this->validator = new Validator();
    }

    private function getRetrieverMock($returnSchema)
    {
        $jsonSchema = json_decode($returnSchema);

        if (JSON_ERROR_NONE < $error = json_last_error()) {
            throw new JsonDecodingException($error);
        }

        $retriever = $this->getMock('JsonSchema\Uri\UriRetriever', array('retrieve'));

        $retriever->expects($this->at(0))
                  ->method('retrieve')
                  ->with($this->equalTo(null), $this->equalTo('http://some.host.at/somewhere/parent'))
                  ->will($this->returnValue($jsonSchema));

        return $retriever;
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testChildExtendsParentValidTest($childSchema, $parentSchema)
    {
        $this->mockRetriever($parentSchema);

        $json = '{"childProp":"infant", "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);

        $this->validator->check($decodedJson, $decodedJsonSchema);
        $this->assertTrue($this->validator->isValid());
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testChildExtendsParentInvalidChildTest($childSchema, $parentSchema)
    {
        $this->mockRetriever($parentSchema);

        $json = '{"childProp":1, "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);

        $this->validator->check($decodedJson, $decodedJsonSchema);
        $this->assertFalse($this->validator->isValid());
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testChildExtendsParentInvalidParentTest($childSchema, $parentSchema)
    {
        $this->mockRetriever($parentSchema);

        $json = '{"childProp":"infant", "parentProp":1}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);

        $this->validator->check($decodedJson, $decodedJsonSchema);
        $this->assertFalse($this->validator->isValid());
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testResolveRelativeUri($childSchema, $parentSchema)
    {
        self::setParentSchemaExtendsValue($parentSchema, 'grandparent');
        $this->mockRetriever($parentSchema);

        $json = '{"childProp":"infant", "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);

        $this->validator->check($decodedJson, $decodedJsonSchema);
        $this->assertTrue($this->validator->isValid());
    }

    private static function setParentSchemaExtendsValue(&$parentSchema, $value)
    {
        $parentSchemaDecoded = json_decode($parentSchema, true);
        $parentSchemaDecoded['extends'] = $value;
        $parentSchema = json_encode($parentSchemaDecoded);
    }

    public function jsonProvider()
    {
        $childSchema = <<<EOF
{
    "type":"object",
    "title":"child",
    "extends":"http://some.host.at/somewhere/parent",
    "properties":
    {
        "childProp":
        {
            "type":"string"
        }
    }
}
EOF;
        $parentSchema = <<<EOF
{
    "type":"object",
    "title":"parent",
    "properties":
    {
        "parentProp":
        {
            "type":"boolean"
        }
    }
}
EOF;

        return array(
            array($childSchema, $parentSchema)
        );
    }

    public function testResolvePointerNoFragment()
    {
        $schema = (object) array(
            'title' => 'schema'
        );

        $retriever = new \JsonSchema\Uri\UriRetriever();
        $this->assertEquals(
            $schema,
            $retriever->resolvePointer(
                $schema, 'http://example.org/schema.json'
            )
        );
    }

    public function testResolvePointerFragment()
    {
        $schema = (object) array(
            'definitions' => (object) array(
                'foo' => (object) array(
                    'title' => 'foo'
                )
            ),
            'title' => 'schema'
        );

        $retriever = new \JsonSchema\Uri\UriRetriever();
        $this->assertEquals(
            $schema->definitions->foo,
            $retriever->resolvePointer(
                $schema, 'http://example.org/schema.json#/definitions/foo'
            )
        );
    }

    /**
     * @expectedException \JsonSchema\Exception\ResourceNotFoundException
     */
    public function testResolvePointerFragmentNotFound()
    {
        $schema = (object) array(
            'definitions' => (object) array(
                'foo' => (object) array(
                    'title' => 'foo'
                )
            ),
            'title' => 'schema'
        );

        $retriever = new \JsonSchema\Uri\UriRetriever();
        $retriever->resolvePointer(
            $schema, 'http://example.org/schema.json#/definitions/bar'
        );
    }

    /**
     * @expectedException \JsonSchema\Exception\ResourceNotFoundException
     */
    public function testResolvePointerFragmentNoArray()
    {
        $schema = (object) array(
            'definitions' => (object) array(
                'foo' => array(
                    'title' => 'foo'
                )
            ),
            'title' => 'schema'
        );

        $retriever = new \JsonSchema\Uri\UriRetriever();
        $retriever->resolvePointer(
            $schema, 'http://example.org/schema.json#/definitions/foo'
        );
    }

    /**
     * @expectedException \JsonSchema\Exception\UriResolverException
     */
    public function testResolveExcessLevelUp()
    {
        $retriever = new \JsonSchema\Uri\UriRetriever();
        $retriever->resolve(
            '../schema.json#', 'http://example.org/schema.json#'
        );
    }

    public function testConfirmMediaTypeAcceptsJsonSchemaType()
    {
        $retriever = $this->getMock('JsonSchema\Uri\UriRetriever', array('getContentType'));

        $retriever->expects($this->at(0))
                ->method('getContentType')
                ->will($this->returnValue('application/schema+json'));

        $this->assertEquals(null, $retriever->confirmMediaType($retriever, null));
    }

    public function testConfirmMediaTypeAcceptsJsonType()
    {
        $retriever = $this->getMock('JsonSchema\Uri\UriRetriever', array('getContentType'));

        $retriever->expects($this->at(0))
                ->method('getContentType')
                ->will($this->returnValue('application/json'));

        $this->assertEquals(null, $retriever->confirmMediaType($retriever, null));
    }

    /**
     * @expectedException \JsonSchema\Exception\InvalidSchemaMediaTypeException
     */
    public function testConfirmMediaTypeThrowsExceptionForUnsupportedTypes()
    {
        $retriever = $this->getMock('JsonSchema\Uri\UriRetriever', array('getContentType'));

        $retriever->expects($this->at(0))
                ->method('getContentType')
                ->will($this->returnValue('text/html'));

        $this->assertEquals(null, $retriever->confirmMediaType($retriever, null));
    }

    private function mockRetriever($schema)
    {
        $retrieverMock = $this->getRetrieverMock($schema);

        $factory = new \ReflectionProperty('JsonSchema\Constraints\BaseConstraint', 'factory');
        $factory->setAccessible(true);
        $factory = $factory->getValue($this->validator);

        $retriever = new \ReflectionProperty('JsonSchema\Constraints\Factory', 'uriRetriever');
        $retriever->setAccessible(true);
        $retriever->setValue($factory, $retrieverMock);
    }

    public function testTranslations()
    {
        $retriever = new UriRetriever();

        $uri = 'http://example.com/foo/bar';
        $translated = 'file://another/bar';

        $retriever->setTranslation('|^https?://example.com/foo/bar#?|', 'file://another/bar');
        $this->assertEquals($translated, $retriever->translate($uri));
    }

    public function testPackageURITranslation()
    {
        $retriever = new UriRetriever();
        $root = sprintf('file://%s/', realpath(__DIR__ . '/../..'));

        $uri = $retriever->translate('package://foo/bar.json');
        $this->assertEquals("${root}foo/bar.json", $uri);
    }

    public function testDefaultDistTranslations()
    {
        $retriever = new UriRetriever();
        $root = sprintf('file://%s/dist/schema/', realpath(__DIR__ . '/../..'));

        $this->assertEquals(
            $root . 'json-schema-draft-03.json',
            $retriever->translate('http://json-schema.org/draft-03/schema#')
        );

        $this->assertEquals(
            $root . 'json-schema-draft-04.json',
            $retriever->translate('http://json-schema.org/draft-04/schema#')
        );
    }

    public function testRetrieveSchemaFromPackage()
    {
        $retriever = new UriRetriever();

        // load schema from package
        $schema = $retriever->retrieve('package://tests/fixtures/foobar.json');
        $this->assertNotFalse($schema);

        // check that the schema was loaded & processed correctly
        $this->assertEquals('454f423bd7edddf0bc77af4130ed9161', md5(json_encode($schema)));
    }

    public function testInvalidContentTypeEndpointsDefault()
    {
        $mock = $this->getMock('JsonSchema\Uri\UriRetriever', array('getContentType'));
        $mock->method('getContentType')->willReturn('Application/X-Fake-Type');
        $retriever = new UriRetriever();

        $this->assertTrue($retriever->confirmMediaType($mock, 'http://json-schema.org/'));
        $this->assertTrue($retriever->confirmMediaType($mock, 'https://json-schema.org/'));
    }

    /**
     * @expectedException \JsonSchema\Exception\InvalidSchemaMediaTypeException
     */
    public function testInvalidContentTypeEndpointsUnknown()
    {
        $mock = $this->getMock('JsonSchema\Uri\UriRetriever', array('getContentType'));
        $mock->method('getContentType')->willReturn('Application/X-Fake-Type');
        $retriever = new UriRetriever();

        $retriever->confirmMediaType($mock, 'http://example.com');
    }

    public function testInvalidContentTypeEndpointsAdded()
    {
        $mock = $this->getMock('JsonSchema\Uri\UriRetriever', array('getContentType'));
        $mock->method('getContentType')->willReturn('Application/X-Fake-Type');
        $retriever = new UriRetriever();
        $retriever->addInvalidContentTypeEndpoint('http://example.com');

        $retriever->confirmMediaType($mock, 'http://example.com');
    }

    public function testSchemaCache()
    {
        $retriever = new UriRetriever();
        $reflector = new \ReflectionObject($retriever);

        // inject a schema cache value
        $schemaCache = $reflector->getProperty('schemaCache');
        $schemaCache->setAccessible(true);
        $schemaCache->setValue($retriever, array('local://test/uri' => 'testSchemaValue'));

        // retrieve from schema cache
        $loadSchema = $reflector->getMethod('loadSchema');
        $loadSchema->setAccessible(true);
        $this->assertEquals(
            'testSchemaValue',
            $loadSchema->invoke($retriever, 'local://test/uri')
        );
    }

    public function testLoadSchemaJSONDecodingException()
    {
        $retriever = new UriRetriever();

        $this->setExpectedException(
            'JsonSchema\Exception\JsonDecodingException',
            'JSON syntax is malformed'
        );
        $schema = $retriever->retrieve('package://tests/fixtures/bad-syntax.json');
    }

    public function testGenerateURI()
    {
        $retriever = new UriRetriever();
        $components = array(
            'scheme' => 'scheme',
            'authority' => 'authority',
            'path' => '/path',
            'query' => '?query',
            'fragment' => '#fragment'
        );
        $this->assertEquals('scheme://authority/path?query#fragment', $retriever->generate($components));
    }

    public function testResolveHTTP()
    {
        $retriever = new UriRetriever();
        $this->assertEquals(
            'http://example.com/schema',
            $retriever->resolve('http://example.com/schema')
        );
    }

    public function combinedURITests()
    {
        return array(
            array('blue', 'http://example.com/red', 'http://example.com/blue'),
            array('blue', 'http://example.com/', 'http://example.com/blue'),
        );
    }

    /**
     * @dataProvider combinedURITests
     */
    public function testResolveCombinedURI($uri, $baseURI, $combinedURI)
    {
        $retriever = new UriRetriever();
        $this->assertEquals($combinedURI, $retriever->resolve($uri, $baseURI));
    }

    public function testIsValidURI()
    {
        $retriever = new UriRetriever();
        $this->assertTrue($retriever->isValid('http://example.com/schema'));
    }
}
