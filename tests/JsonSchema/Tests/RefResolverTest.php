<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

class RefResolverTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider resolveProvider
	 */
	public function testResolve($input, $methods)
	{
		$resolver = $this->getMock('JsonSchema\RefResolver', array_keys($methods));
		foreach ($methods as $methodName => $methodInvocationCount) {
			$resolver->expects($this->exactly($methodInvocationCount))
				->method($methodName);
		}
		$resolver->resolve($input);
	}

	public function resolveProvider() {
		return array(
			'non-object' => array(
				'string',
				array(
					'resolveRef' => 0,
					'resolveProperty' => 0,
					'resolveArrayOfSchemas' => 0,
					'resolveObjectOfSchemas' => 0
				)
			),
			'empty object' => array(
				(object) array(),
				array(
					'resolveRef' => 1,
					'resolveProperty' => 4,
					'resolveArrayOfSchemas' => 4,
					'resolveObjectOfSchemas' => 3
				)
			)
		);
	}

	/**
	 * Helper method for resolve* methods
	 */
	public function helperResolveMethods($method, $input, $calls) {
		$resolver = $this->getMock('JsonSchema\RefResolver', array('resolve'));
		$resolver->expects($this->exactly($calls[$method]))
			->method('resolve');
		$resolver->$method($input, 'testProp', 'http://example.com/');
	}

	/**
	 * @dataProvider testSchemas
	 */
	public function testResolveArrayOfSchemas($input, $calls) {
		$this->helperResolveMethods('resolveArrayOfSchemas', $input, $calls);
	}

	/**
	 * @dataProvider testSchemas
	 */
	public function testResolveObjectOfSchemas($input, $calls) {
		$this->helperResolveMethods('resolveObjectOfSchemas', $input, $calls);
	}

	public function testSchemas() {
		return array(
			'non-object' => array(
				(object) array(
					'testProp' => 'string'
				),
				array(
					'resolveArrayOfSchemas' => 0,
					'resolveObjectOfSchemas' => 0,
					'resolveProperty' => 0
				)
			),
			'undefined' => array(
				(object) array(
				),
				array(
					'resolveArrayOfSchemas' => 0,
					'resolveObjectOfSchemas' => 0,
					'resolveProperty' => 0
				)
			),
			'empty object' => array(
				(object) array(
					'testProp' => (object) array()
				),
				array(
					'resolveArrayOfSchemas' => 0,
					'resolveObjectOfSchemas' => 0,
					'resolveProperty' => 1
				)
			),
			'filled object' => array(
				(object) array(
					'testProp' => (object) array(
						'one' => array(),
						'two' => array()
					)
				),
				array(
					'resolveArrayOfSchemas' => 0,
					'resolveObjectOfSchemas' => 2,
					'resolveProperty' => 1
				)
			),
			'empty array' => array(
				(object) array(
					'testProp' => array()
				),
				array(
					'resolveArrayOfSchemas' => 0,
					'resolveObjectOfSchemas' => 0,
					'resolveProperty' => 1
				)
			),
			'filled array' => array(
				(object) array(
					'testProp' => array(1, 2, 3)
				),
				array(
					'resolveArrayOfSchemas' => 3,
					'resolveObjectOfSchemas' => 0,
					'resolveProperty' => 1
				)
			)
		);
	}

	/**
	 * @dataProvider refProvider
	 */
	public function testResolveRef($expected, $input) {
		$resolver = $this->getMock('JsonSchema\RefResolver', array('fetchRef'));
		$resolver->expects($this->any())
			->method('fetchRef')
			->will($this->returnValue((object) array(
				'this was' => array('added', 'because'),
				'the' => (object) array('$ref resolved' => true)
			)));
		$resolver->resolveRef($input, 'http://example.com');
		$this->assertEquals($expected, $input);
	}

	public function refProvider() {
		return array(
			'no ref' => array(
				(object) array('test' => 'one'),
				(object) array('test' => 'one')
			),
			// The $ref is not removed here
			'empty ref' => array(
				(object) array(
					'test' => 'two',
					'$ref' => ''
				),
				(object) array(
					'test' => 'two',
					'$ref' => ''
				)
			),
			// $ref is removed
			'qualified ref' => array(
				(object) array(
					'this is' => 'another test',
					'this was' => array('added', 'because'),
					'the' => (object) array('$ref resolved' => true)
				),
				(object) array(
					'$ref' => 'http://example.com/',
					'this is' => 'another test'
				)
			),
		);
	}

	public function testMerge() {
		$a = (object) array('a' => '1');
		$b = new \stdClass;
		RefResolver::merge($a, $b);
		$this->assertEquals((object)array('a'=>'1'), $a);

		$a = (object) array('a' => '1');
		$b = (object) array('a' => '2');
		RefResolver::merge($a, $b);
		$this->assertEquals((object)array('a'=>'1'), $a);

		$a = (object) array('a' => array(1,2,3));
		$b = (object) array('a' => array(4));
		RefResolver::merge($a, $b);
		$this->assertEquals((object)array('a'=>array(4,1,2,3)), $a); // $b values are prependet :(  not nice but no issue

		$a = new \stdClass;
		$b = (object) array('a' => array(4));
		RefResolver::merge($a, $b);
		$this->assertEquals((object)array('a'=>array(4)), $a);

		$a = (object) array('a' => array(1,2,3));
		$b = new \stdClass;
		RefResolver::merge($a, $b);
		$this->assertEquals((object)array('a'=>array(1,2,3)), $a);

		$a = (object) array('a' => 'in a');
		$b = (object) array('b' => 'from b');
		RefResolver::merge($a, $b);
		$this->assertEquals((object)array('a' => 'in a', 'b' => 'from b'), $a);

		$a = null;
		$b = (object) array('b' => 'from b');
		RefResolver::merge($a, $b);
		$this->assertEquals(null, $a);

		$a = (object) array('a' => 'in a');
		$b = null;
		RefResolver::merge($a, $b);
		$this->assertEquals((object) array('a' => 'in a'), $a);

		$a = (object) array('a' => '1', 'c'=>(object)array('d'=>'from a'));
		$b = (object) array('a' => '1', 'c'=>(object)array('d'=>'from b'));
		RefResolver::merge($a, $b);
		$this->assertEquals((object) array('a' => '1', 'c'=>(object)array('d'=>'from a')), $a);

		$a = (object) array('a' => '1');
		$b = (object) array('a' => '1', 'c'=>(object)array('d'=>'from b'));
		RefResolver::merge($a, $b);
		$this->assertEquals((object) array('a' => '1', 'c'=>(object)array('d'=>'from b')), $a);

		$a = (object) array('a' => '1');
		$b = (object) array('a' => '1', 'c'=>'from b');
		RefResolver::merge($a, $b);
		$this->assertEquals((object) array('a' => '1', 'c'=>'from b'), $a);

		$a = (object) array('a' => '1', 'c'=>'from a');
		$b = (object) array('a' => '1');
		RefResolver::merge($a, $b);
		$this->assertEquals((object) array('a' => '1', 'c'=>'from a'), $a);

		$a = (object) array('fields' => array('1 from a', '2 from a'));
		$b = (object) array('fields' => array('1 from b', '2 from b'));
		RefResolver::merge($a, $b);
		$this->assertEquals((object) array('fields' => array('1 from b', '2 from b', '1 from a', '2 from a')), $a);
	}
}
