<?php

namespace JsonSchema\Tests;

use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidateWithAssocSchema()
    {
        $schema = json_decode('{"properties":{"propertyOne":{"type":"array","items":[{"type":"string"}]}}}', true);
        $data = json_decode('{"propertyOne":[42]}', true);

        $validator = new Validator();
        $validator->validate($data, $schema);

        $this->assertFalse($validator->isValid(), 'Validation succeeded, but should have failed.');
    }

    public function testBadAssocSchemaInput()
    {
        if (version_compare(phpversion(), '5.5.0', '<')) {
            $this->markTestSkipped('PHP versions < 5.5.0 trigger an error on json_encode issues');
        }
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM has no problem with encoding resources');
        }
        $schema = array('propertyOne' => fopen('php://stdout', 'w'));
        $data = json_decode('{"propertyOne":[42]}', true);

        $validator = new Validator();

        $this->setExpectedException('\JsonSchema\Exception\InvalidArgumentException');
        $validator->validate($data, $schema);
    }

    public function testCheck()
    {
        $schema = json_decode('{"type":"string"}');
        $data = json_decode('42');

        $validator = new Validator();
        $validator->check($data, $schema);

        $this->assertFalse($validator->isValid(), 'Validation succeeded, but should have failed.');
    }

    public function testCoerce()
    {
        $schema = json_decode('{"type":"integer"}');
        $data = json_decode('"42"');

        $validator = new Validator();
        $validator->coerce($data, $schema);

        $this->assertTrue($validator->isValid(), 'Validation failed, but should have succeeded.');
    }
}
