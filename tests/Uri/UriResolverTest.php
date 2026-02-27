<?php

namespace JsonSchema\Tests\Uri;

use JsonSchema\Uri\UriResolver;
use PHPUnit\Framework\TestCase;

class UriResolverTest extends TestCase
{
    public function setUp()
    {
        $this->resolver = new UriResolver();
    }

    public function testParse()
    {
        $this->assertEquals(
            array(
                'scheme'    => 'http',
                'authority' => 'example.org',
                'path'      => '/path/to/file.json'
            ),
            $this->resolver->parse('http://example.org/path/to/file.json')
        );
    }

    public function testParseAnchor()
    {
        $this->assertEquals(
            array(
                'scheme'    => 'http',
                'authority' => 'example.org',
                'path'      => '/path/to/file.json',
                'query'     => '',
                'fragment'  => 'foo'
            ),
            $this->resolver->parse('http://example.org/path/to/file.json#foo')
        );
    }

    public function testCombineRelativePathWithBasePath()
    {
        $this->assertEquals(
            '/foo/baz.json',
            UriResolver::combineRelativePathWithBasePath(
                'baz.json',
                '/foo/bar.json'
            )
        );
    }

    public function testCombineRelativePathWithBasePathAbsolute()
    {
        $this->assertEquals(
            '/baz/data.json',
            UriResolver::combineRelativePathWithBasePath(
                '/baz/data.json',
                '/foo/bar.json'
            )
        );
    }

    public function testCombineRelativePathWithBasePathRelativeSub()
    {
        $this->assertEquals(
            '/foo/baz/data.json',
            UriResolver::combineRelativePathWithBasePath(
                'baz/data.json',
                '/foo/bar.json'
            )
        );
    }

    public function testCombineRelativePathWithBasePathNoPath()
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

    public function testResolveAbsoluteUri()
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                'http://example.org/foo/bar.json',
                null
            )
        );
    }

    /**
     * @expectedException \JsonSchema\Exception\UriResolverException
     */
    public function testResolveRelativeUriNoBase()
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                'bar.json',
                null
            )
        );
    }

    public function testResolveRelativeUriBaseDir()
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                'bar.json',
                'http://example.org/foo/'
            )
        );
    }

    public function testResolveRelativeUriBaseFile()
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                'bar.json',
                'http://example.org/foo/baz.json'
            )
        );
    }

    public function testResolveAnchor()
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json#baz',
            $this->resolver->resolve(
                '#baz',
                'http://example.org/foo/bar.json'
            )
        );
    }

    public function testResolveAnchorWithFile()
    {
        $this->assertEquals(
            'http://example.org/foo/baz.json#baz',
            $this->resolver->resolve(
                'baz.json#baz',
                'http://example.org/foo/bar.json'
            )
        );
    }

    public function testResolveAnchorAnchor()
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json#bazinga',
            $this->resolver->resolve(
                '#bazinga',
                'http://example.org/foo/bar.json#baz'
            )
        );
    }

    public function testResolveEmpty()
    {
        $this->assertEquals(
            'http://example.org/foo/bar.json',
            $this->resolver->resolve(
                '',
                'http://example.org/foo/bar.json'
            )
        );
    }

    public function testReversable()
    {
        $uri = 'scheme://user:password@authority/path?query#fragment';
        $split = $this->resolver->parse($uri);

        // check that the URI was split as expected
        $this->assertEquals(array(
            'scheme' => 'scheme',
            'authority' => 'user:password@authority',
            'path' => '/path',
            'query' => 'query',
            'fragment' => 'fragment'
        ), $split);

        // check that the recombined URI matches the original input
        $this->assertEquals($uri, $this->resolver->generate($split));
    }

    public function testRelativeFileAsRoot()
    {
        $this->assertEquals(
            'file://' . getcwd() . '/src/JsonSchema/Validator.php',
            $this->resolver->resolve(
                'Validator.php',
                'src/JsonSchema/SchemaStorage.php'
            )
        );
    }

    public function testRelativeDirectoryAsRoot()
    {
        $this->assertEquals(
            'file://' . getcwd() . '/src/JsonSchema/Validator.php',
            $this->resolver->resolve(
                'Validator.php',
                'src/JsonSchema'
            )
        );
    }

    public function testRelativeNonExistentFileAsRoot()
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
