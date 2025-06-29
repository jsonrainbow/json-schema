<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Uri;

use JsonSchema\Exception\InvalidSchemaMediaTypeException;
use JsonSchema\Exception\JsonDecodingException;
use JsonSchema\Exception\ResourceNotFoundException;
use JsonSchema\Exception\UriResolverException;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class UriRetrieverTest extends TestCase
{
    protected $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    private function getRetrieverMock($returnSchema)
    {
        $jsonSchema = json_decode($returnSchema);

        if (JSON_ERROR_NONE < $error = json_last_error()) {
            throw new JsonDecodingException($error);
        }

        $retriever = $this->createMock(\JsonSchema\Uri\UriRetriever::class);

        $retriever->expects($this->at(0))
                  ->method('retrieve')
                  ->with($this->equalTo(null), $this->equalTo('http://some.host.at/somewhere/parent'))
                  ->willReturn($jsonSchema);

        return $retriever;
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testChildExtendsParentValidTest($childSchema, $parentSchema): void
    {
        $this->mockRetriever($parentSchema);

        $json = '{"childProp":"infant", "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);

        $this->validator->validate($decodedJson, $decodedJsonSchema);
        $this->assertTrue($this->validator->isValid());
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testChildExtendsParentInvalidChildTest($childSchema, $parentSchema): void
    {
        $this->mockRetriever($parentSchema);

        $json = '{"childProp":1, "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);

        $this->validator->validate($decodedJson, $decodedJsonSchema);
        $this->assertFalse($this->validator->isValid());
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testChildExtendsParentInvalidParentTest($childSchema, $parentSchema): void
    {
        $this->mockRetriever($parentSchema);

        $json = '{"childProp":"infant", "parentProp":1}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);

        $this->validator->validate($decodedJson, $decodedJsonSchema);
        $this->assertFalse($this->validator->isValid());
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testResolveRelativeUri($childSchema, $parentSchema): void
    {
        self::setParentSchemaExtendsValue($parentSchema, 'grandparent');
        $this->mockRetriever($parentSchema);

        $json = '{"childProp":"infant", "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);

        $this->validator->validate($decodedJson, $decodedJsonSchema);
        $this->assertTrue($this->validator->isValid());
    }

    private static function setParentSchemaExtendsValue(&$parentSchema, $value): void
    {
        $parentSchemaDecoded = json_decode($parentSchema, true);
        $parentSchemaDecoded['extends'] = $value;
        $parentSchema = json_encode($parentSchemaDecoded);
    }

    public function jsonProvider(): array
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

        return [
            [$childSchema, $parentSchema]
        ];
    }

    public function testResolvePointerNoFragment(): void
    {
        $schema = (object) [
            'title' => 'schema'
        ];

        $retriever = new UriRetriever();
        $this->assertEquals(
            $schema,
            $retriever->resolvePointer(
                $schema, 'http://example.org/schema.json'
            )
        );
    }

    public function testResolvePointerFragment(): void
    {
        $schema = (object) [
            'definitions' => (object) [
                'foo' => (object) [
                    'title' => 'foo'
                ]
            ],
            'title' => 'schema'
        ];

        $retriever = new UriRetriever();
        $this->assertEquals(
            $schema->definitions->foo,
            $retriever->resolvePointer(
                $schema, 'http://example.org/schema.json#/definitions/foo'
            )
        );
    }

    public function testResolvePointerFragmentNotFound(): void
    {
        $schema = (object) [
            'definitions' => (object) [
                'foo' => (object) [
                    'title' => 'foo'
                ]
            ],
            'title' => 'schema'
        ];

        $retriever = new UriRetriever();

        $this->expectException(ResourceNotFoundException::class);
        $retriever->resolvePointer(
            $schema, 'http://example.org/schema.json#/definitions/bar'
        );
    }

    public function testResolvePointerFragmentNoArray(): void
    {
        $schema = (object) [
            'definitions' => (object) [
                'foo' => [
                    'title' => 'foo'
                ]
            ],
            'title' => 'schema'
        ];

        $retriever = new UriRetriever();

        $this->expectException(ResourceNotFoundException::class);
        $retriever->resolvePointer(
            $schema, 'http://example.org/schema.json#/definitions/foo'
        );
    }

    public function testResolveExcessLevelUp(): void
    {
        $retriever = new UriRetriever();

        $this->expectException(UriResolverException::class);
        $retriever->resolve(
            '../schema.json#', 'http://example.org/schema.json#'
        );
    }

    public function testConfirmMediaTypeAcceptsJsonSchemaType(): void
    {
        $uriRetriever = $this->createMock(\JsonSchema\Uri\Retrievers\UriRetrieverInterface::class);
        $retriever = new UriRetriever();

        $uriRetriever->expects($this->at(0))
                ->method('getContentType')
                ->willReturn('application/schema+json');

        $this->assertEquals(null, $retriever->confirmMediaType($uriRetriever, null));
    }

    public function testConfirmMediaTypeAcceptsJsonType(): void
    {
        $uriRetriever = $this->createMock(\JsonSchema\Uri\Retrievers\UriRetrieverInterface::class);
        $retriever = new UriRetriever();

        $uriRetriever->expects($this->at(0))
                ->method('getContentType')
                ->willReturn('application/json');

        $this->assertEquals(null, $retriever->confirmMediaType($uriRetriever, null));
    }

    public function testConfirmMediaTypeThrowsExceptionForUnsupportedTypes(): void
    {
        $uriRetriever = $this->createMock(\JsonSchema\Uri\Retrievers\UriRetrieverInterface::class);
        $retriever = new UriRetriever();
        $uriRetriever->expects($this->at(0))
                ->method('getContentType')
                ->willReturn('text/html');

        $this->expectException(InvalidSchemaMediaTypeException::class);

        $retriever->confirmMediaType($uriRetriever, null);
    }

    private function mockRetriever($schema): void
    {
        $retrieverMock = $this->getRetrieverMock($schema);

        $factory = new \ReflectionProperty(\JsonSchema\Constraints\BaseConstraint::class, 'factory');
        $factory->setAccessible(true);
        $factory = $factory->getValue($this->validator);

        $retriever = new \ReflectionProperty(\JsonSchema\Constraints\Factory::class, 'uriRetriever');
        $retriever->setAccessible(true);
        $retriever->setValue($factory, $retrieverMock);
    }

    public function testTranslations(): void
    {
        $retriever = new UriRetriever();

        $uri = 'http://example.com/foo/bar';
        $translated = 'file://another/bar';

        $retriever->setTranslation('|^https?://example.com/foo/bar#?|', 'file://another/bar');
        $this->assertEquals($translated, $retriever->translate($uri));
    }

    public function testPackageURITranslation(): void
    {
        $retriever = new UriRetriever();
        $root = sprintf('file://%s/', realpath(__DIR__ . '/../..'));

        $uri = $retriever->translate('package://foo/bar.json');
        $this->assertEquals("{$root}foo/bar.json", $uri);
    }

    public function testDefaultDistTranslations(): void
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

    public function testRetrieveSchemaFromPackage(): void
    {
        $retriever = new UriRetriever();

        // load schema from package
        $schema = $retriever->retrieve('package://tests/fixtures/foobar.json');
        $this->assertNotFalse($schema);

        // check that the schema was loaded & processed correctly
        $this->assertEquals('454f423bd7edddf0bc77af4130ed9161', md5(json_encode($schema)));
    }

    public function testInvalidContentTypeEndpointsDefault(): void
    {
        $mock = $this->createMock(\JsonSchema\Uri\Retrievers\UriRetrieverInterface::class);
        $mock->method('getContentType')->willReturn('Application/X-Fake-Type');
        $retriever = new UriRetriever();

        $this->assertTrue($retriever->confirmMediaType($mock, 'http://json-schema.org/'));
        $this->assertTrue($retriever->confirmMediaType($mock, 'https://json-schema.org/'));
    }

    public function testInvalidContentTypeEndpointsUnknown(): void
    {
        $mock = $this->createMock(\JsonSchema\Uri\Retrievers\UriRetrieverInterface::class);
        $mock->method('getContentType')->willReturn('Application/X-Fake-Type');
        $retriever = new UriRetriever();

        $this->expectException(InvalidSchemaMediaTypeException::class);
        $retriever->confirmMediaType($mock, 'http://example.com');
    }

    public function testInvalidContentTypeEndpointsAdded(): void
    {
        $mock = $this->createMock(\JsonSchema\Uri\Retrievers\UriRetrieverInterface::class);
        $mock->method('getContentType')->willReturn('Application/X-Fake-Type');
        $retriever = new UriRetriever();
        $retriever->addInvalidContentTypeEndpoint('http://example.com');

        $result = $retriever->confirmMediaType($mock, 'http://example.com');

        self::assertTrue($result);
    }

    public function testSchemaCache(): void
    {
        $retriever = new UriRetriever();
        $reflector = new \ReflectionObject($retriever);

        // inject a schema cache value
        $schemaCache = $reflector->getProperty('schemaCache');
        $schemaCache->setAccessible(true);
        $schemaCache->setValue($retriever, ['local://test/uri' => 'testSchemaValue']);

        // retrieve from schema cache
        $loadSchema = $reflector->getMethod('loadSchema');
        $loadSchema->setAccessible(true);
        $this->assertEquals(
            'testSchemaValue',
            $loadSchema->invoke($retriever, 'local://test/uri')
        );
    }

    public function testLoadSchemaJSONDecodingException(): void
    {
        $retriever = new UriRetriever();

        $this->expectException(\JsonSchema\Exception\JsonDecodingException::class);
        $this->expectExceptionMessage('JSON syntax is malformed');

        $retriever->retrieve('package://tests/fixtures/bad-syntax.json');
    }

    public function testGenerateURI(): void
    {
        $retriever = new UriRetriever();
        $components = [
            'scheme' => 'scheme',
            'authority' => 'authority',
            'path' => '/path',
            'query' => '?query',
            'fragment' => '#fragment'
        ];
        $this->assertEquals('scheme://authority/path?query#fragment', $retriever->generate($components));
    }

    public function testResolveHTTP(): void
    {
        $retriever = new UriRetriever();
        $this->assertEquals(
            'http://example.com/schema',
            $retriever->resolve('http://example.com/schema')
        );
    }

    public function combinedURITests(): array
    {
        return [
            ['blue', 'http://example.com/red', 'http://example.com/blue'],
            ['blue', 'http://example.com/', 'http://example.com/blue'],
        ];
    }

    /**
     * @dataProvider combinedURITests
     */
    public function testResolveCombinedURI($uri, $baseURI, $combinedURI): void
    {
        $retriever = new UriRetriever();
        $this->assertEquals($combinedURI, $retriever->resolve($uri, $baseURI));
    }

    public function testIsValidURI(): void
    {
        $retriever = new UriRetriever();
        $this->assertTrue($retriever->isValid('http://example.com/schema'));
    }
}
