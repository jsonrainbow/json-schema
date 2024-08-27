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

    protected function setUp(): void
    {
        $this->factory = new Factory();
    }

    /**
     * @dataProvider constraintNameProvider
     *
     * @param string $constraintName
     * @param string $expectedClass
     */
    public function testCreateInstanceForConstraintName($constraintName, $expectedClass): void
    {
        $constraint = $this->factory->createInstanceFor($constraintName);

        $this->assertInstanceOf($expectedClass, $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
    }

    public function constraintNameProvider(): array
    {
        return [
            ['array', 'JsonSchema\Constraints\CollectionConstraint'],
            ['collection', 'JsonSchema\Constraints\CollectionConstraint'],
            ['object', 'JsonSchema\Constraints\ObjectConstraint'],
            ['type', 'JsonSchema\Constraints\TypeConstraint'],
            ['undefined', 'JsonSchema\Constraints\UndefinedConstraint'],
            ['string', 'JsonSchema\Constraints\StringConstraint'],
            ['number', 'JsonSchema\Constraints\NumberConstraint'],
            ['enum', 'JsonSchema\Constraints\EnumConstraint'],
            ['const', 'JsonSchema\Constraints\ConstConstraint'],
            ['format', 'JsonSchema\Constraints\FormatConstraint'],
            ['schema', 'JsonSchema\Constraints\SchemaConstraint'],
        ];
    }

    /**
     * @dataProvider invalidConstraintNameProvider
     *
     * @param string $constraintName
     */
    public function testExceptionWhenCreateInstanceForInvalidConstraintName($constraintName): void
    {
        $this->expectException('JsonSchema\Exception\InvalidArgumentException');
        $this->factory->createInstanceFor($constraintName);
    }

    public function invalidConstraintNameProvider(): array
    {
        return [
            ['invalidConstraintName'],
        ];
    }

    public function testSetConstraintClassExistsCondition(): void
    {
        $this->expectException(\JsonSchema\Exception\InvalidArgumentException::class);

        $this->factory->setConstraintClass('string', 'SomeConstraint');
    }

    public function testSetConstraintClassImplementsCondition(): void
    {
        $this->expectException(\JsonSchema\Exception\InvalidArgumentException::class);

        $this->factory->setConstraintClass('string', 'JsonSchema\Tests\Constraints\MyBadConstraint');
    }

    public function testSetConstraintClassInstance(): void
    {
        $this->factory->setConstraintClass('string', 'JsonSchema\Tests\Constraints\MyStringConstraint');
        $constraint = $this->factory->createInstanceFor('string');
        $this->assertInstanceOf('JsonSchema\Tests\Constraints\MyStringConstraint', $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
    }

    public function testCheckMode(): void
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
