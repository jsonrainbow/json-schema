<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Uri;

use JsonSchema\Uri\UriResolver;
use PHPUnit\Framework\TestCase;

class UriResolverTest extends TestCase
{
    /**
     * @var UriResolver
     */
    private $resolver;

    public function setUp(): void
    {
        $this->resolver = new UriResolver();
    }

    public function testParse(): void
    {
        $this->assertEquals(
            [
                'scheme'    => 'http',
                'authority' => 'example.org',
                'path'      => '/path/to/file.json'
            ],
            $this->resolver->parse('http://example.org/path/to/file.json')
        );
    }

    public function testParseAnchor(): void
    {
        $this->assertEquals(
            [
                'scheme'    => 'http',
                'authority' => 'example.org',
                'path'      => '/path/to/file.json',
                'query'     => '',
                'fragment'  => 'foo'
            ],
            $this->resolver->parse('http://example.org/path/to/file.json#foo')
        );
    }

    public function testCombineRelativePathWithBasePath(): void
    {
        $this->assertEquals(
            '/foo/baz.json',
            UriResolver::combineRelativePathWithBasePath(
                'baz.json',
                '/foo/bar.json'
            )
        );
    }

    public function testCombineRelativePathWithBasePathAbsolute(): void
    {
        $this->assertEquals(
            '/baz/data.json',
            UriResolver::combineRelativePathWithBasePath(
                '/baz/data.json',
                '/foo/bar.json'
            )
        );
    }

    public function testCombineRelativePathWithBasePathRelativeSub(): void
    {
        $this->assertEquals(
            '/foo/baz/data.json',
            UriResolver::combineRelativePathWithBasePath(
                'baz/data.json',
                '/foo/bar.json'
            )
        );
    }

    public function testCombineRelativePathWithBasePathNoPath(): void
    {
        //needed for anchor-only urls
        $this->assertEquals(
            '/foo/bar.json',
            UriResolver::combineRelativePathWithBasePath(
                '',
                '/foo/bar.json'
            )
        );
    }

    /**
     * Covers https://github.com/justinrainbow/json-schema/issues/557
     * Relative paths yield wrong result.
     */
    public function testCombineRelativePathWithBasePathTraversingUp(): void
    {
        $this->assertEquals(
            '/var/packages/schema/UuidSchema.json',
            UriResolver::combineRelativePathWithBasePath(
                '../../../schema/UuidSchema.json',
                '/var/packages/foo/tests/UnitTests/DemoData/../../../schema/Foo/FooSchema_latest.json'
            )
        );
    }

    public function testResolveAbsoluteUri(): void
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                'http://example.org/foo/bar.json',
                null
            )
        );
    }

    public function testResolveRelativeUriNoBase(): void
    {
        $this->expectException(\JsonSchema\Exception\UriResolverException::class);
        $this->resolver->resolve('bar.json', null);
    }

    public function testResolveRelativeUriBaseDir(): void
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                'bar.json',
                'http://example.org/foo/'
            )
        );
    }

    public function testResolveRelativeUriBaseFile(): void
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                'bar.json',
                'http://example.org/foo/baz.json'
            )
        );
    }

    public function testResolveAnchor(): void
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json#baz',
            $this->resolver->resolve(
                '#baz',
                'http://example.org/foo/bar.json'
            )
        );
    }

    public function testResolveAnchorWithFile(): void
    {
        $this->assertEquals(
            'http://example.org/foo/baz.json#baz',
            $this->resolver->resolve(
                'baz.json#baz',
                'http://example.org/foo/bar.json'
            )
        );
    }

    public function testResolveAnchorAnchor(): void
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json#bazinga',
            $this->resolver->resolve(
                '#bazinga',
                'http://example.org/foo/bar.json#baz'
            )
        );
    }

    public function testResolveEmpty(): void
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                '',
                'http://example.org/foo/bar.json'
            )
        );
    }

    public function testResolveRelativeUriAgainstBaseWithoutPath(): void
    {
        // RFC 3986 section 5.3: a base uri with an authority but an empty path merges onto '/'
        $this->assertEquals(
            'internal://mySchema/bar.json',
            $this->resolver->resolve(
                'bar.json',
                'internal://mySchema'
            )
        );
    }

    public function testResolveAnchorAgainstBaseWithoutPath(): void
    {
        // an anchor-only reference must not gain a path of its own
        $this->assertEquals(
            'internal://mySchema#/definitions/foo',
            $this->resolver->resolve(
                '#/definitions/foo',
                'internal://mySchema'
            )
        );
    }

    /**
     * @dataProvider fragmentAgainstUrnBaseProvider
     */
    public function testResolveFragmentAgainstUrnBase(string $expected, string $uri, string $baseUri): void
    {
        // a urn has no authority, so it must neither gain one nor be taken for a local file path
        $this->assertEquals($expected, $this->resolver->resolve($uri, $baseUri));
    }

    public function fragmentAgainstUrnBaseProvider(): \Generator
    {
        yield 'json pointer' => [
            'urn:uuid:deadbeef-1234-ffff-ffff-4321feebdaed#/definitions/bar',
            '#/definitions/bar',
            'urn:uuid:deadbeef-1234-ffff-ffff-4321feebdaed',
        ];
        yield 'plain name' => [
            'urn:uuid:deadbeef-1234-ffff-ffff-4321feebdaed#something',
            '#something',
            'urn:uuid:deadbeef-1234-ffff-ffff-4321feebdaed',
        ];
        yield 'base fragment is replaced' => [
            'urn:example:foo#/definitions/bar',
            '#/definitions/bar',
            'urn:example:foo#something',
        ];
        yield 'q-component is kept' => [
            'urn:example:weather?=op=map&lat=39.56&lon=-104.85#/definitions/bar',
            '#/definitions/bar',
            'urn:example:weather?=op=map&lat=39.56&lon=-104.85',
        ];
    }

    public function testReversable(): void
    {
        $uri = 'scheme://user:password@authority/path?query#fragment';
        $split = $this->resolver->parse($uri);

        // check that the URI was split as expected
        $this->assertEquals([
            'scheme' => 'scheme',
            'authority' => 'user:password@authority',
            'path' => '/path',
            'query' => 'query',
            'fragment' => 'fragment'
        ], $split);

        // check that the recombined URI matches the original input
        $this->assertEquals($uri, $this->resolver->generate($split));
    }

    public function testRelativeFileAsRoot(): void
    {
        $this->assertEquals(
            'file://' . getcwd() . '/src/JsonSchema/Validator.php',
            $this->resolver->resolve(
                'Validator.php',
                'src/JsonSchema/SchemaStorage.php'
            )
        );
    }

    public function testRelativeDirectoryAsRoot(): void
    {
        $this->assertEquals(
            'file://' . getcwd() . '/src/JsonSchema/Validator.php',
            $this->resolver->resolve(
                'Validator.php',
                'src/JsonSchema'
            )
        );
    }

    public function testRelativeNonExistentFileAsRoot(): void
    {
        $this->assertEquals(
            'file://' . getcwd() . '/resolved.file',
            $this->resolver->resolve(
                'resolved.file',
                'test.file'
            )
        );
    }
}
