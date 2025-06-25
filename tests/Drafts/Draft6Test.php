<?php

namespace Drafts;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Tests\Drafts\BaseDraftTestCase;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Validator;

class Draft6Test extends BaseDraftTestCase
{
    protected $schemaSpec = 'http://json-schema.org/draft-06/schema#';
    protected $validateSchema = true;

    /**
     * @dataProvider getInvalidTests
     */
    public function testInvalidCases($input, $schema, $checkMode = Constraint::CHECK_MODE_NORMAL, $errors = []): void
    {
        $checkMode = $checkMode === null ? Constraint::CHECK_MODE_NORMAL : $checkMode;
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }
        $checkMode |= Constraint::CHECK_MODE_STRICT;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        // add `$schema` if missing
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $checkValue = json_decode($input, false);
        $errorMask = $validator->validate($checkValue, $schema);

        $this->assertTrue((bool) ($errorMask & Validator::ERROR_DOCUMENT_VALIDATION), 'Document is invalid: ' .print_r($validator->getErrors(), true));
        $this->assertGreaterThan(0, $validator->numErrors());

        if ([] !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getInvalidForAssocTests
     */
    public function testInvalidCasesUsingAssoc($input, $schema, $checkMode = Constraint::CHECK_MODE_TYPE_CAST, $errors = []): void
    {
        $checkMode = $checkMode === null ? Constraint::CHECK_MODE_TYPE_CAST : $checkMode;
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }
        if (!($checkMode & Constraint::CHECK_MODE_TYPE_CAST)) {
            $this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_TYPE_CAST"');
        }
        $checkMode |= Constraint::CHECK_MODE_STRICT;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');
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
     */
    public function testValidCases(string $input, string $schema, int $checkMode = Constraint::CHECK_MODE_NORMAL): void
    {
        $schemaObject = json_decode($schema);
        $checkMode |= Constraint::CHECK_MODE_STRICT;
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }
        $checkMode |= Constraint::CHECK_MODE_STRICT;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schemaObject));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');
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
     */
    public function testValidCasesUsingAssoc($input, $schema, $checkMode = Constraint::CHECK_MODE_TYPE_CAST): void
    {
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }
        if (!($checkMode & Constraint::CHECK_MODE_TYPE_CAST)) {
            $this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_TYPE_CAST"');
        }
        $checkMode |= Constraint::CHECK_MODE_STRICT;

        $schema = json_decode($schema);
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schema), new UriResolver());
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $value = json_decode($input, true);
        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));

        $errorMask = $validator->validate($value, $schema);
        $this->assertEquals(0, $errorMask, $this->validatorErrorsToString($validator));
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }
    /**
     * {@inheritdoc}
     */
    protected function getFilePaths(): array
    {
        return [
            realpath(__DIR__ . self::RELATIVE_TESTS_ROOT . '/draft6'),
            realpath(__DIR__ . self::RELATIVE_TESTS_ROOT . '/draft6/optional')
        ];
    }

    protected function getSkippedTests(): array
    {
        return [];
    }
}
