<?php

namespace JsonSchema\Tests;

use JsonSchema\Validator;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getInvalidTests
     */
    public function testInvalidCases($input, $schema, $checkMode = Validator::CHECK_MODE_NORMAL, $errors = array())
    {
        $validator = new Validator($checkMode);

        $validator->check(json_decode($input), json_decode($schema));

        if (array() !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(),true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCases($input, $schema, $checkMode = Validator::CHECK_MODE_NORMAL)
    {
        $validator = new Validator($checkMode);

        $validator->check(json_decode($input), json_decode($schema));
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    abstract public function getValidTests();

    abstract public function getInvalidTests();
}