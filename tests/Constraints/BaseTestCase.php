<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     */
    public function testInvalidCases(string $input, string $schema, ?int $checkMode = Constraint::CHECK_MODE_NORMAL, array $errors = []): void
    {
        $checkMode = $checkMode ?? Constraint::CHECK_MODE_NORMAL;
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema, false)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $checkValue = json_decode($input, false);
        $errorMask = $validator->validate($checkValue, $schema);

        $this->assertTrue((bool) ($errorMask & Validator::ERROR_DOCUMENT_VALIDATION));
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
    public function testValidCases($input, $schema, $checkMode = Constraint::CHECK_MODE_NORMAL): void
    {
        if ($this->validateSchema) {
            $checkMode |= Constraint::CHECK_MODE_VALIDATE_SCHEMA;
        }
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $checkValue = json_decode($input);
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

        $schema = json_decode($schema);
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schema), new UriResolver());
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');
        if (is_object($schema) && !isset($schema->{'$schema'})) {
            $schema->{'$schema'} = $this->schemaSpec;
        }

        $value = json_decode($input, true);
        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));

        $errorMask = $validator->validate($value, $schema);
        $this->assertEquals(0, $errorMask);
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
}
