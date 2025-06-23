<?php

declare(strict_types=1);

namespace JsonSchema\Tests;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidateWithAssocSchema(): void
    {
        $schema = json_decode('{"properties":{"propertyOne":{"type":"array","items":[{"type":"string"}]}}}', true);
        $data = json_decode('{"propertyOne":[42]}', true);

        $validator = new Validator();
        $validator->validate($data, $schema);

        $this->assertFalse($validator->isValid(), 'Validation succeeded, but should have failed.');
    }

    public function testValidateWithAssocSchemaWithRelativeRefs(): void
    {
        $schema = json_decode(file_get_contents(__DIR__ . '/fixtures/relative.json'), true);
        $data = json_decode('{"foo":{"foo": "bar"}}', false);

        $validator = new Validator();
        $validator->validate($data, $schema);

        $this->assertTrue($validator->isValid(), 'Validation failed, but should have succeeded.');
    }

    public function testBadAssocSchemaInput(): void
    {
        $schema = ['propertyOne' => fopen('php://stdout', 'wb')];
        $data = json_decode('{"propertyOne":[42]}', true);

        $validator = new Validator();

        $this->expectException(InvalidArgumentException::class);
        $validator->validate($data, $schema);
    }

    public function testDeprecatedCheckDelegatesToValidate(): void
    {
        $schema = json_decode('{"type":"string"}');
        $data = json_decode('42');

        $validator = new Validator();
        $validator->check($data, $schema);

        $this->assertFalse($validator->isValid(), 'Validation succeeded, but should have failed.');
    }

    public function testDeprecatedCoerceDelegatesToValidate(): void
    {
        $schema = json_decode('{"type":"integer"}');
        $data = json_decode('"42"');

        $validator = new Validator();
        $validator->coerce($data, $schema);

        $this->assertTrue($validator->isValid(), 'Validation failed, but should have succeeded.');
    }
}
