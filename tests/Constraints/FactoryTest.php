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
use PHPUnit_Framework_TestCase as TestCase;


/**
 * Class MyBadConstraint
 * @package JsonSchema\Tests\Constraints
 */
class MyBadConstraint {}

/**
 * Class MyStringConstraint
 * @package JsonSchema\Tests\Constraints
 */
class MyStringConstraint extends Constraint {
  public function check($value, $schema = null, JsonPointer $path = null, $i = null){}
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
     * @return void
     */
    public function testCreateInstanceForConstraintName($constraintName, $expectedClass)
    {
        $constraint = $this->factory->createInstanceFor(Constraint::CHECK_MODE_NORMAL, $constraintName);

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
            array('validator', 'JsonSchema\Validator'),
        );
    }

    /**
     * @dataProvider invalidConstraintNameProvider
     *
     * @param string $constraintName
     * @return void
     */
    public function testExceptionWhenCreateInstanceForInvalidConstraintName($constraintName)
    {
        $this->setExpectedException('JsonSchema\Exception\InvalidArgumentException');
        $this->factory->createInstanceFor(Constraint::CHECK_MODE_NORMAL, $constraintName);
    }

    public function invalidConstraintNameProvider() {
      return array(
        array('invalidConstraintName'),
      );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetConstraintClassExistsCondition()
    {
      $this->factory->setConstraintClass('string', 'SomeConstraint');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetConstraintClassImplementsCondition()
    {
      $this->factory->setConstraintClass('string', 'JsonSchema\Tests\Constraints\MyBadConstraint');
    }

    public function testSetConstraintClassInstance()
    {
      $this->factory->setConstraintClass('string', 'JsonSchema\Tests\Constraints\MyStringConstraint');
      $constraint = $this->factory->createInstanceFor(Constraint::CHECK_MODE_NORMAL, 'string');
      $this->assertInstanceOf('JsonSchema\Tests\Constraints\MyStringConstraint', $constraint);
      $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
    }
}
