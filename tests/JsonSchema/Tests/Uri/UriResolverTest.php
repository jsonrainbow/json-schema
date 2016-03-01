<?php
namespace JsonSchema\Tests\Uri;

use JsonSchema\Uri\UriResolver;

class UriResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resolver = new UriResolver();
    }

    /**
     * @dataProvider uriProvider
     */
    public function testExtractLocation($uri, $location, $fragment)
    {
        $this->assertEquals($location, $this->resolver->extractLocation($uri));
    }

    /**
     * @dataProvider uriProvider
     */
    public function testExtractFragment($uri, $location, $fragment)
    {
        $this->assertEquals($fragment, $this->resolver->extractFragment($uri));
    }

    public function uriProvider()
    {
        return array(
            'No Fragment' => array(
                'http://example.org/path/to/file.json',
                'http://example.org/path/to/file.json',
                '',
            ),
            'With Fragment' => array(
                'http://example.org/path/to/file.json#foo',
                'http://example.org/path/to/file.json',
                '#foo',
            ),
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
}
?>
