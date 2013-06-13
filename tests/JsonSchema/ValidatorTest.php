<?php

/**
 * integration test for JsonSchema\Validator
 *
 * run tests against the example schema hierarchy in /tests/fixtures/
 *
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
	function fixturePath($fileName) {
		return dirname(__DIR__) .'/fixtures/'. $fileName .'.json';
	}
	function fixture($fileName) {
		return json_decode(file_get_contents($this->fixturePath($fileName)));
	}

	function setUp(){
		$resolver = new RefResolver();
		$this->schemaUri = 'file://'. $this->fixturePath('Employee');
		$retriever = new Uri\UriRetriever;
		$this->schema = $retriever->retrieve($this->schemaUri);

		$refResolver = new RefResolver($retriever);
		$refResolver->resolve($this->schema, $this->schemaUri);

		$this->data = $this->fixture('employee_instance');
	}

	function assertValid($data) {
		$validator = new Validator();
		$validator->check($data, $this->schema);
		$this->assertEquals(array(), $validator->getErrors(), print_r($validator->getErrors(), true));
		$this->assertTrue($validator->isValid());
	}

		function assertNotValid($data, $errors) {
		$validator = new Validator();
		$validator->check($data, $this->schema);
		$this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
		$this->assertFalse($validator->isValid());
	}

	function testResolvedSchema() {
		$expectedResolvedSchema = $this->fixture('resolved_Employee_schema');
		$expectedResolvedSchema->id = $this->schemaUri;
		$this->assertEquals($expectedResolvedSchema, $this->schema, 'expected resolved schema');
	}

	function testEmployeeShouldBeValidate() {
		$this->assertValid($this->data);
	}

	function testEmployeeMissingOfficeAddress() {
		unset($this->data->person->office_address);

		$this->assertNotValid(
			$this->data
			,array(array(
		  	 'property' => 'person.office_address'
	    	,'message'  => 'is missing and it is required'
	  	))
		);
	}
}