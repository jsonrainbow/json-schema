<?php

namespace JsonSchema\Tests;

use JsonSchema\Validator;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getInvalidTests
     */
    public function testInvalidCases($input, $schema, $checkMode = null, $errors = array())
    {
        if (null === $checkMode) {
            $checkMode = Validator::CHECK_MODE_NORMAL;
        }

        $validator = new Validator();
        $validator->checkMode = $checkMode;

        $result = $validator->validate(json_decode($input), json_decode($schema));
        if (array() !== $errors) {
            $this->assertEquals($errors, $result->errors, var_export($result, true));
        }
        $this->assertFalse($result->valid, var_export($result, true));
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCases($input, $schema, $checkMode = null)
    {
        if (null === $checkMode) {
            $checkMode = Validator::CHECK_MODE_NORMAL;
        }

        $validator = new Validator();
        $validator->checkMode = $checkMode;

        $result = $validator->validate(json_decode($input), json_decode($schema));
        $this->assertTrue($result->valid, var_export($result, true));
    }

    abstract public function getValidTests();

    abstract public function getInvalidTests();
}