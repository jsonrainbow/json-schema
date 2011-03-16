<?php

abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getInvalidTests
     */
    public function testInvalidCases($input, $schema, $errors = array())
    {
        $result = JsonSchema::validate(json_decode($input), json_decode($schema));
        if (array() !== $errors) {
            $this->assertEquals($errors, $result->errors);
        }
        $this->assertFalse($result->valid, var_export($result, true));
    }
    
    /**
     * @dataProvider getValidTests
     */
    public function testValidCases($input, $schema, $checkMode = null)
    {    
        if (null === $checkMode) {
            $checkMode = JsonSchema::CHECK_MODE_NORMAL;
        }
        JsonSchema::$checkMode = $checkMode;
        $result = JsonSchema::validate(json_decode($input), json_decode($schema));
        $this->assertTrue($result->valid, var_export($result, true));
    }
    
    abstract public function getValidTests();
    
    abstract public function getInvalidTests();
}