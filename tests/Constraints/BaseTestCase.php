<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Validator;

/**
 * @package JsonSchema\Tests\Constraints
 */
abstract class BaseTestCase extends VeryBaseTestCase
{
    /**
     * @dataProvider getInvalidTests
     */
    public function testInvalidCases($input, $schema, $checkMode = Constraint::CHECK_MODE_NORMAL, $errors = array())
    {
        $checkMode = $checkMode === null ? Constraint::CHECK_MODE_NORMAL : $checkMode;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $validator->check(json_decode($input), $schema);

        if (array() !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(),true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getInvalidForAssocTests
     */
    public function testInvalidCasesUsingAssoc($input, $schema, $checkMode = Constraint::CHECK_MODE_TYPE_CAST, $errors = array())
    {
        $checkMode = $checkMode === null ? Constraint::CHECK_MODE_TYPE_CAST : $checkMode;
        if ($checkMode !== Constraint::CHECK_MODE_TYPE_CAST) {
            $this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_TYPE_CAST"');
        }

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $validator->check(json_decode($input, true), $schema);

        if (array() !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCases($input, $schema, $checkMode = Constraint::CHECK_MODE_NORMAL)
    {
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $validator->check(json_decode($input), $schema);

        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getValidForAssocTests
     */
    public function testValidCasesUsingAssoc($input, $schema, $checkMode = Constraint::CHECK_MODE_TYPE_CAST)
    {
        if ($checkMode !== Constraint::CHECK_MODE_TYPE_CAST) {
            $this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_TYPE_CAST"');
        }

        $schema = json_decode($schema);
        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schema), new UriResolver);
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $value = json_decode($input, true);
        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));

        $validator->check($value, $schema);
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @return array[]
     */
    abstract public function getValidTests();

    /**
     * @return array[]
     */
    public function getValidForAssocTests()
    {
        return $this->getValidTests();
    }

    /**
     * @return array[]
     */
    abstract public function getInvalidTests();

    /**
     * @return array[]
     */
    public function getInvalidForAssocTests()
    {
        return $this->getInvalidTests();
    }
}
