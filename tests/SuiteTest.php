<?php
		/**
		 * Runs the JSON Schema Test Suite
		 * Install JSON-Schema-Test-Suite git submodele:
		 * $> git submodule update --init
		 */
class SuiteTest extends \PHPUnit_Framework_TestCase {


	public static function schemaSuiteTestProvider() {
		//error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

		if(!is_dir(__DIR__.'/suite/tests/draft3')) {
			$this->markTestSkipped(
				'The language independent JSON-Schema-Test-Suite is not installed. See README.md.'
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
		//return array($tests[242]);
		return $tests;
	}

	 /**
	 * @dataProvider schemaSuiteTestProvider
	 */
	 function testSchemaSuite($test) {
	 		//echo "\n"; print_r($test);
	 		$this->setName($test->suite->description.': '.($test->valid?'valid':'not valid').' : '.$test->description.' |');
			$validator = new JsonSchema\Validator();
			$validator->check($test->data, $test->suite->schema);
			// test.valid, result.valid, util.inspect(result, true, null))
			if($test->valid) {
				$this->assertTrue($validator->isValid()
					,"data: ".print_r($test->data, true)
						."\nschema: ".print_r($test->suite->schema, true)
						."\nerrors: ".print_r($validator->getErrors(), true)
				);
			} else {
				$this->assertFalse($validator->isValid()
					,"data: ".print_r($test->data, true)."\nschema: ".print_r($test->suite->schema, true)
				);
			}
	 }
}