<?php
/**
 * Work in progress!
 * Runs the JSON Schema Test Suite
 * Install JSON-Schema-Test-Suite git submodele:
 * $> git submodule update --init
 * @author Jan Mentzel <jan@hypercharge.net>
 */
class SuiteTest extends \PHPUnit_Framework_TestCase {

	/**
	* mixed
	*  int
	*     -1     ... run all test cases. or use false
	*     0 to n ... run only one speciffic testcase, will switch to verbose=true
	*                e.g.  PICK = 145;  run test case #145
	*  string
	*     regexp ... run tests with suite.description matching the regexp
	*                e.g.  PICK = '/multiple dependencies/';
	*
	* Hint: you can turn off SuiteTest with something like PICK = '/^NO TESTS$/'
	*/
	const PICK = -1;

	static private $verbose = false;

	private $draft3Dir;

	public static function schemaSuiteTestProvider() {
		if(!is_dir(__DIR__.'/suite/tests/draft3')) {
			self::markTestSkipped(
				"The language independent JSON-Schema-Test-Suite is not installed.\nSee README.md for install instructions."
			);
			return;
		}
		$tests = array();
		$paths = array(
				__DIR__.'/suite/tests/draft3'
				,__DIR__.'/suite/tests/draft3/optional'
		);
		$ignoredFiles = array('optional', 'zeroTerminatedFloats.json');

		$errors = array();

		foreach($paths as $path) {
			//echo "\npath: $path\n";
			foreach (glob($path.'/*.json') as $file) {
				//echo "\nfile: $file\n";
				$suites = json_decode(file_get_contents($file));
				foreach($suites as $suite) {
					// pick speciffic tests if wanted
					if(is_string(self::PICK) && !preg_match(self::PICK, $suite->description)) continue;

					//echo "\nsuite: ",$suite->description, "\n";
					foreach($suite->tests as $test) {
						if(!$test->description) continue;
						//echo "\t",$test->description, "\n";
						$test->suite = new stdClass();
						$test->suite->description = $suite->description;
						$test->suite->schema      = $suite->schema;
						array_push($tests, array($test));
					}
				}
			}
		}
		//print_r($tests);

		if(self::PICK < 0 || self::PICK === false) {
			return $tests;
		}
		self::$verbose = true;
		if(is_int(self::PICK)) {
			return array($tests[self::PICK]);
		}
		return $tests;
	}

	 /**
	 * @dataProvider schemaSuiteTestProvider
	 */
	 function testSchemaSuite($test) {
	 		if(self::$verbose) {
	 			echo "\n"; print_r($test);
	 		}
	 		$this->setName($test->suite->description.': '.($test->valid?'valid':'not valid').' : '.$test->description.' |');
			$validator = new JsonSchema\Validator();

			// resolve http:// or file:// $ref and extends
			$refResolver = new JsonSchema\RefResolver();
			$refResolver->resolve($test->suite->schema);

			// echo "\nresolved schema: ";
			// print_r($test->suite->schema);

			// suppress errors because of php wargings of invalid-regexp tests
			$turnOffWarnings = preg_match('/regular expression/', $test->description) && !$test->valid;
			if($turnOffWarnings) $flags = error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

			$validator->check($test->data, $test->suite->schema);

			if($turnOffWarnings) error_reporting($flags);

			if($test->valid) {
				$msg = self::$verbose
					? "data: ".print_r($test->data, true)
					 	."\nschema: ".print_r($test->suite->schema, true)
					 	."\nerrors: ".print_r($validator->getErrors(), true)
					: null;
				$this->assertTrue($validator->isValid(), $msg);
			} else {
				$msg = self::$verbose
					? "data: ".print_r($test->data, true)
					 	."\nschema: ".print_r($test->suite->schema, true)
					: null;
				$this->assertFalse($validator->isValid(), $msg);
			}
	 }
}