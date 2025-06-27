<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use Generator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Validator;

abstract class BaseTestCase extends VeryBaseTestCase
{
    protected $schemaSpec = 'http://json-schema.org/draft-04/schema#';
    protected $validateSchema = false;

    /**
     * @dataProvider getInvalidTests
     *
     * @param ?int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     */
    public function testInvalidCases(string $input, string $schema, ?int $checkMode = Constraint::CHECK_MODE_NORMAL, array $errors = []): void
    {
        $checkMode = $checkMode ?? Constraint::CHECK_MODE_NORMAL;
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }

        $schema = json_decode($schema, false);
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schema));
        $schema = $schemaStorage->getSchema($schema->id ?? 'http://www.my-domain.com/schema.json');
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $checkValue = json_decode($input, false);
        $errorMask = $validator->validate($checkValue, $schema);

        $this->assertTrue((bool) ($errorMask & Validator::ERROR_DOCUMENT_VALIDATION), 'Document is invalid');
        $this->assertGreaterThan(0, $validator->numErrors());

        if ([] !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getInvalidForAssocTests
     *
     * @param ?int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     */
    public function testInvalidCasesUsingAssoc(string $input, string $schema, ?int $checkMode = Constraint::CHECK_MODE_TYPE_CAST, array $errors = []): void
    {
        $checkMode = $checkMode ?? Constraint::CHECK_MODE_TYPE_CAST;
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }
        if (!($checkMode & Constraint::CHECK_MODE_TYPE_CAST)) {
            $this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_TYPE_CAST"');
        }

        $schema = json_decode($schema, false);
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schema));
        $schema = $schemaStorage->getSchema($schema->id ?? 'http://www.my-domain.com/schema.json');
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $checkValue = json_decode($input, true);
        $errorMask = $validator->validate($checkValue, $schema);

        $this->assertTrue((bool) ($errorMask & Validator::ERROR_DOCUMENT_VALIDATION));
        $this->assertGreaterThan(0, $validator->numErrors());

        if ([] !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getValidTests
     *
     * @param ?int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     */
    public function testValidCases(string $input, string $schema, int $checkMode = Constraint::CHECK_MODE_NORMAL): void
    {
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }

        $schema = json_decode($schema, false);
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schema));
        $schema = $schemaStorage->getSchema($schema->id ?? 'http://www.my-domain.com/schema.json');
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $checkValue = json_decode($input, false);
        $errorMask = $validator->validate($checkValue, $schema);
        $this->assertEquals(0, $errorMask);

        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getValidForAssocTests
     *
     * @param ?int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     */
    public function testValidCasesUsingAssoc(string $input, string $schema, ?int $checkMode = Constraint::CHECK_MODE_TYPE_CAST): void
    {
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }
        if (!($checkMode & Constraint::CHECK_MODE_TYPE_CAST)) {
            $this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_TYPE_CAST"');
        }

        $schema = json_decode($schema, false);
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schema), new UriResolver());
        $schema = $schemaStorage->getSchema($schema->id ?? 'http://www.my-domain.com/schema.json');
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $value = json_decode($input, true);
        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));

        $errorMask = $validator->validate($value, $schema);
        $this->assertEquals(0, $errorMask, $this->validatorErrorsToString($validator));
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    abstract public function getValidTests(): Generator;

    public function getValidForAssocTests(): Generator
    {
        yield from $this->getValidTests();
    }

    abstract public function getInvalidTests(): Generator;

    public function getInvalidForAssocTests(): Generator
    {
        yield from $this->getInvalidTests();
    }

    protected function validatorErrorsToString(Validator $validator): string
    {
        return implode(
            ', ',
            array_map(
                static function (array $error) { return $error['message']; }, $validator->getErrors()
            )
        );
    }
}
