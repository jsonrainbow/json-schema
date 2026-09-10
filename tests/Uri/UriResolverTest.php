<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Uri;

use JsonSchema\Uri\UriResolver;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testResolveEmptyDropsTheFragmentOfTheBase(): void
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json?q=1',
            $this->resolver->resolve(
                '',
                'http://example.org/foo/bar.json?q=1#frag'
            )
        );
    }

    /**
     * @dataProvider queryAndFragmentInheritanceCases
     */
    #[DataProvider('queryAndFragmentInheritanceCases')]
    public function testResolveDoesNotInheritQueryAndFragmentFromBase(string $expected, string $uri, string $baseUri): void
    {
        $this->assertEquals($expected, $this->resolver->resolve($uri, $baseUri));
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function queryAndFragmentInheritanceCases(): array
    {
        $base = 'http://example.org/foo/x.json?old#frag';

        return [
            // RFC 3986 section 5.3: neither is inherited when the reference has a path
            'plain reference' => ['http://example.org/foo/bar.json', 'bar.json', $base],
            'reference with its own query' => ['http://example.org/foo/bar.json?new', 'bar.json?new', $base],
            'reference with its own fragment' => ['http://example.org/foo/bar.json#f2', 'bar.json#f2', $base],
            'reference with both' => ['http://example.org/foo/bar.json?new#f2', 'bar.json?new#f2', $base],
            'absolute path reference' => ['http://example.org/abs.json', '/abs.json', $base],
            // a reference without a path or query of its own keeps the query of the base
            'bare fragment' => [
                'http://example.org/schema.json?q=1#/definitions/x',
                '#/definitions/x',
                'http://example.org/schema.json?q=1',
            ],
            // an empty query is supplied, not absent, so it replaces the one of the base
            'empty query' => ['http://example.org/foo/x.json', '?', $base],
            'empty query with a fragment' => ['http://example.org/foo/x.json#f2', '?#f2', $base],
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
