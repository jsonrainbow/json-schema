<?php

namespace JsonSchema\Tests\Uri;

use JsonSchema\Uri\UriResolver;

class UriResolverTest extends \PHPUnit_Framework_TestCase {

	function testParseWithFragment() {
		$resolver = new UriResolver();
		$this->assertEquals(
			array('scheme' => 'file', 'authority' => '', 'path' => '/the/directory', 'query'=>'', 'fragment' => '/the/fragment')
			,$resolver->parse('file:///the/directory#/the/fragment')
		);
	}

	function testParseWithQuery() {
		$resolver = new UriResolver();
		$this->assertEquals(
			array('scheme' => 'file', 'authority' => '', 'path' => '/the/directory', 'query'=>'the=query&key=val')
			,$resolver->parse('file:///the/directory?the=query&key=val')
		);
	}

	function testParseWithHttp() {
		$resolver = new UriResolver();
		$this->assertEquals(
			array('scheme' => 'http', 'authority' => 'localhost:1234', 'path' => '/the/directory')
			,$resolver->parse('http://localhost:1234/the/directory')
		);
	}

	function testParseFragmentOnly() {
		$resolver = new UriResolver();
		$this->assertEquals(
			array('scheme' => '', 'authority' => '', 'path' => '', 'query'=>'', 'fragment' => '/theFragment')
			,$resolver->parse('#/theFragment')
		);
	}

	function testResolveWithBothHttp() {
		$resolver = new UriResolver();
		$this->setExpectedException('JsonSchema\Exception\UriResolverException', "Unable to resolve URI '/the/path' from base ''");
		$this->assertEquals('http://localhost:1234/the/path', $resolver->resolve('/the/path', 'http://localhost:1234'));
	}

	function testResolveWithValidHttpBaseUri() {
		$resolver = new UriResolver();

		$this->assertEquals('http://localhost:1234/the/other/schema.json' , $resolver->resolve('/the/other/schema.json'   , 'http://localhost:1234/'));
		$this->assertEquals('http://localhost:1234/other/foo.json'        , $resolver->resolve('/other/foo.json' , 'http://localhost:1234/schema.json'));
		$this->assertEquals('http://localhost:1234/foo.json#/fragment'    , $resolver->resolve('/foo.json#/fragment' , 'http://localhost:1234/schma.json'));
		$this->assertEquals('http://localhost:1234/three/dirs/bar.json'   , $resolver->resolve('bar.json' , 'http://localhost:1234/three/dirs/schema.json'));
		$this->assertEquals('http://localhost:1234/sourceSchema.json#/localTarget'    , $resolver->resolve('#/localTarget' , 'http://localhost:1234/sourceSchema.json#/sourceNode'));
	}

	function testResolveWithValidFileBaseUri() {
		$resolver = new UriResolver();
		$this->assertEquals('file:///base/uri/foo.json', $resolver->resolve('foo.json', 'file:///base/uri/dir.json'));
		$this->assertEquals('file:///other-dir/foo.json', $resolver->resolve('file:///other-dir/foo.json', 'file:///base/uri/dir.json'));

	}

	function testCombineRelativePathWithBasePath() {
		$this->assertEquals('/foo/bar/boo', UriResolver::combineRelativePathWithBasePath('boo', '/foo/bar/'));
		$this->assertEquals('/foo/bar/'   , UriResolver::combineRelativePathWithBasePath('', '/foo/bar/'));
		$this->assertEquals('/foo/bar'       , UriResolver::combineRelativePathWithBasePath('', '/foo/bar'));
		// ??? imho /boo
		$this->assertEquals('/foo/bar/boo' , UriResolver::combineRelativePathWithBasePath('/boo', '/foo/bar/'));
		$this->assertEquals('/foo.json' , UriResolver::combineRelativePathWithBasePath('', '/foo.json'));

	}
}