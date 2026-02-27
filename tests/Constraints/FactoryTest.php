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
use JsonSchema\Entity\JsonPointer;
use PHPUnit\Framework\TestCase;

/**
 * Class MyBadConstraint
 *
 * @package JsonSchema\Tests\Constraints
 */
class MyBadConstraint
{
}

/**
 * Class MyStringConstraint
 *
 * @package JsonSchema\Tests\Constraints
 */
class MyStringConstraint extends Constraint
{
    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null)
    {
    }
}

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = new Factory();
    }

    /**
     * @dataProvider constraintNameProvider
     *
     * @param string $constraintName
     * @param string $expectedClass
     */
    public function testCreateInstanceForConstraintName($constraintName, $expectedClass)
    {
        $constraint = $this->factory->createInstanceFor($constraintName);

        $this->assertInstanceOf($expectedClass, $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
    }

    public function constraintNameProvider()
    {
        return array(
            array('array', 'JsonSchema\Constraints\CollectionConstraint'),
            array('collection', 'JsonSchema\Constraints\CollectionConstraint'),
            array('object', 'JsonSchema\Constraints\ObjectConstraint'),
            array('type', 'JsonSchema\Constraints\TypeConstraint'),
            array('undefined', 'JsonSchema\Constraints\UndefinedConstraint'),
            array('string', 'JsonSchema\Constraints\StringConstraint'),
            array('number', 'JsonSchema\Constraints\NumberConstraint'),
            array('enum', 'JsonSchema\Constraints\EnumConstraint'),
            array('format', 'JsonSchema\Constraints\FormatConstraint'),
            array('schema', 'JsonSchema\Constraints\SchemaConstraint'),
        );
    }

    /**
     * @dataProvider invalidConstraintNameProvider
     *
     * @param string $constraintName
     */
    public function testExceptionWhenCreateInstanceForInvalidConstraintName($constraintName)
    {
        $this->setExpectedException('JsonSchema\Exception\InvalidArgumentException');
        $this->factory->createInstanceFor($constraintName);
    }

    public function invalidConstraintNameProvider()
    {
        return array(
            array('invalidConstraintName'),
        );
    }

    /**
     * @expectedException \JsonSchema\Exception\InvalidArgumentException
     */
    public function testSetConstraintClassExistsCondition()
    {
        $this->factory->setConstraintClass('string', 'SomeConstraint');
    }

    /**
     * @expectedException \JsonSchema\Exception\InvalidArgumentException
     */
    public function testSetConstraintClassImplementsCondition()
    {
        $this->factory->setConstraintClass('string', 'JsonSchema\Tests\Constraints\MyBadConstraint');
    }

    public function testSetConstraintClassInstance()
    {
        $this->factory->setConstraintClass('string', 'JsonSchema\Tests\Constraints\MyStringConstraint');
        $constraint = $this->factory->createInstanceFor('string');
        $this->assertInstanceOf('JsonSchema\Tests\Constraints\MyStringConstraint', $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
    }

    public function testCheckMode()
    {
        $f = new Factory();

        // test default value
        $this->assertEquals(Constraint::CHECK_MODE_NORMAL, $f->getConfig());

        // test overriding config
        $f->setConfig(Constraint::CHECK_MODE_COERCE_TYPES);
        $this->assertEquals(Constraint::CHECK_MODE_COERCE_TYPES, $f->getConfig());

        // test adding config
        $f->addConfig(Constraint::CHECK_MODE_NORMAL);
        $this->assertEquals(Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_COERCE_TYPES, $f->getConfig());

        // test getting filtered config
        $this->assertEquals(Constraint::CHECK_MODE_NORMAL, $f->getConfig(Constraint::CHECK_MODE_NORMAL));

        // test removing config
        $f->removeConfig(Constraint::CHECK_MODE_COERCE_TYPES);
        $this->assertEquals(Constraint::CHECK_MODE_NORMAL, $f->getConfig());

        // test resetting to defaults
        $f->setConfig(Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_TYPE_CAST);
        $f->setConfig();
        $this->assertEquals(Constraint::CHECK_MODE_NORMAL, $f->getConfig());
    }
}
