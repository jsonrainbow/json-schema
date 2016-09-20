<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Validator;
use Prophecy\Argument;

/**
 * @package JsonSchema\Tests\Constraints
 */
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var object */
    private $jsonSchemaDraft03;

    /** @var object */
    private $jsonSchemaDraft04;

    /**
     * @dataProvider getInvalidTests
     */
    public function testInvalidCases($input, $schema, $checkMode = Constraint::CHECK_MODE_NORMAL, $errors = array())
    {
        $checkMode = $checkMode === null ? Constraint::CHECK_MODE_NORMAL : $checkMode;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator($checkMode, $schemaStorage);
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

        $validator = new Validator($checkMode, $schemaStorage);
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

        $validator = new Validator($checkMode, $schemaStorage);
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
        $validator = new Validator($checkMode, $schemaStorage);

        $validator->check($value, $schema);
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

	/**
	 * @dataProvider getValidCoerceForAssocTests
	 */
	public function testValidCoerceCasesUsingAssoc($input, $schema, $checkMode = Constraint::CHECK_MODE_COERCE)
	{
		if ($checkMode !== Constraint::CHECK_MODE_COERCE) {
			$this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_COERCE"');
		}

		$schema = json_decode($schema);
		$schemaStorage = new SchemaStorage($this->getUriRetrieverMock($schema), new UriResolver);
		$schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

		$value = json_decode($input, true);
		$validator = new Validator($checkMode, $schemaStorage);

		$validator->check($value, $schema);
		$this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
	}

	/**
	 * @dataProvider getValidCoerceTests
	 */
	public function testValidCoerceCases($input, $schema, $checkMode = Constraint::CHECK_MODE_COERCE)
	{
		if ($checkMode !== Constraint::CHECK_MODE_COERCE) {
			$this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_COERCE"');
		}

		$schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
		$schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

		$validator = new Validator($checkMode, $schemaStorage);
		$validator->check(json_decode($input), $schema);

		$this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
	}

	/**
	 * @dataProvider getInvalidCoerceTests
	 */
	public function testInvalidCoerceCases($input, $schema, $checkMode = Constraint::CHECK_MODE_COERCE, $errors = array())
	{
		$checkMode = $checkMode === null ? Constraint::CHECK_MODE_COERCE : $checkMode;

		$schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
		$schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

		$validator = new Validator($checkMode, $schemaStorage);
		$validator->check(json_decode($input), $schema);

		if (array() !== $errors) {
			$this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(),true));
		}
		$this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
	}

	/**
	 * @dataProvider getInvalidCoerceForAssocTests
	 */
	public function testInvalidCoerceCasesUsingAssoc($input, $schema, $checkMode = Constraint::CHECK_MODE_COERCE, $errors = array())
	{
		$checkMode = $checkMode === null ? Constraint::CHECK_MODE_COERCE : $checkMode;
		if ($checkMode !== Constraint::CHECK_MODE_COERCE) {
			$this->markTestSkipped('Test indicates that it is not for "CHECK_MODE_COERCE"');
		}

		$schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
		$schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

		$validator = new Validator($checkMode, $schemaStorage);
		$validator->check(json_decode($input, true), $schema);

		if (array() !== $errors) {
			$this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
		}
		$this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
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
	public function getValidCoerceForAssocTests()
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

	/**
	 * @return array[]
	 */
	public function getInvalidCoerceForAssocTests()
	{
		return $this->getInvalidTests();
	}

	/**
	 * @return array[]
	 */
	public function getValidCoerceTests()
	{
		return $this->getValidTests();
	}

	/**
	 * @return array[]
	 */
	public function getInvalidCoerceTests()
	{
		return $this->getInvalidTests();
	}

    /**
     * @param object $schema
     * @return object
     */
    private function getUriRetrieverMock($schema)
    {
        $relativeTestsRoot = realpath(__DIR__ . '/../../vendor/json-schema/JSON-Schema-Test-Suite/remotes');

        $jsonSchemaDraft03 = $this->getJsonSchemaDraft03();
        $jsonSchemaDraft04 = $this->getJsonSchemaDraft04();

        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve('http://www.my-domain.com/schema.json')
            ->willReturn($schema)
            ->shouldBeCalled();
        $uriRetriever->retrieve(Argument::any())
            ->will(function ($args) use ($jsonSchemaDraft03, $jsonSchemaDraft04, $relativeTestsRoot) {
                if ('http://json-schema.org/draft-03/schema' === $args[0]) {
                    return $jsonSchemaDraft03;
                } elseif ('http://json-schema.org/draft-04/schema' === $args[0]) {
                    return $jsonSchemaDraft04;
                } elseif (0 === strpos($args[0], 'http://localhost:1234')) {
                    $urlParts = parse_url($args[0]);
                    return json_decode(file_get_contents($relativeTestsRoot . $urlParts['path']));
                } elseif (0 === strpos($args[0], 'http://www.my-domain.com')) {
                    $urlParts = parse_url($args[0]);
                    return json_decode(file_get_contents($relativeTestsRoot . '/folder' . $urlParts['path']));
                }
            });
        return $uriRetriever->reveal();
    }

    /**
     * @return object
     */
    private function getJsonSchemaDraft03()
    {
        if (!$this->jsonSchemaDraft03) {
            $this->jsonSchemaDraft03 = json_decode(
                file_get_contents(__DIR__ . '/../fixtures/json-schema-draft-03.json')
            );
        }

        return $this->jsonSchemaDraft03;
    }

    /**
     * @return object
     */
    private function getJsonSchemaDraft04()
    {
        if (!$this->jsonSchemaDraft04) {
            $this->jsonSchemaDraft04 = json_decode(
                file_get_contents(__DIR__ . '/../fixtures/json-schema-draft-04.json')
            );
        }

        return $this->jsonSchemaDraft04;
    }
}
