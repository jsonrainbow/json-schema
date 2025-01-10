<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MyBadConstraint
{
}

class MyStringConstraint extends Constraint
{
    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null)
    {
    }
}

class FactoryTest extends TestCase
{
    /**
     * @dataProvider constraintNameProvider
     */
    public function testCreateInstanceForConstraintName(string $constraintName, string $expectedClass): void
    {
        $factory = new Factory();
        $constraint = $factory->createInstanceFor($constraintName);

        $this->assertInstanceOf($expectedClass, $constraint);
        $this->assertInstanceOf(ConstraintInterface::class, $constraint);
    }

    public static function constraintNameProvider(): \Generator
    {
        yield 'Array' => ['array', Constraints\CollectionConstraint::class];
        yield 'Collection' => ['collection', Constraints\CollectionConstraint::class];
        yield 'Object' => ['object', Constraints\ObjectConstraint::class];
        yield 'Type' => ['type', Constraints\TypeConstraint::class];
        yield 'Undefined' => ['undefined', Constraints\UndefinedConstraint::class];
        yield 'String' => ['string', Constraints\StringConstraint::class];
        yield 'Number' => ['number', Constraints\NumberConstraint::class];
        yield 'Enum' => ['enum', Constraints\EnumConstraint::class];
        yield 'Const' => ['const', Constraints\ConstConstraint::class];
        yield 'Format' => ['format', Constraints\FormatConstraint::class];
        yield 'Schema' => ['schema', Constraints\SchemaConstraint::class];
    }

    /**
     * @dataProvider invalidConstraintNameProvider
     */
    public function testExceptionWhenCreateInstanceForInvalidConstraintName(string $constraintName): void
    {
        $factory = new Factory();

        $this->expectException(InvalidArgumentException::class);

        $factory->createInstanceFor($constraintName);
    }

    public static function invalidConstraintNameProvider(): \Generator
    {
        yield 'InvalidConstraint' => ['invalidConstraintName'];
    }

    public function testSetConstraintClassExistsCondition(): void
    {
        $factory = new Factory();

        $this->expectException(\JsonSchema\Exception\InvalidArgumentException::class);

        $factory->setConstraintClass('string', 'SomeConstraint');
    }

    public function testSetConstraintClassImplementsCondition(): void
    {
        $factory = new Factory();

        $this->expectException(\JsonSchema\Exception\InvalidArgumentException::class);

        $factory->setConstraintClass('string', MyBadConstraint::class);
    }

    public function testSetConstraintClassInstance(): void
    {
        $factory = new Factory();
        $factory->setConstraintClass('string', MyStringConstraint::class);

        $constraint = $factory->createInstanceFor('string');

        $this->assertInstanceOf(MyStringConstraint::class, $constraint);
        $this->assertInstanceOf(ConstraintInterface::class, $constraint);
    }

    public function testCheckModeDefaultConfig(): void
    {
        $f = new Factory();

        $this->assertEquals(Constraint::CHECK_MODE_NORMAL, $f->getConfig());
    }

    public function testCheckModeWhenOverridingConfig(): void
    {
        $f = new Factory();

        $f->setConfig(Constraint::CHECK_MODE_COERCE_TYPES);

        $this->assertEquals(Constraint::CHECK_MODE_COERCE_TYPES, $f->getConfig());
    }

    public function testCheckModeWhenAddingConfig(): void
    {
        $f = new Factory();

        $f->setConfig(Constraint::CHECK_MODE_COERCE_TYPES);
        $f->addConfig(Constraint::CHECK_MODE_NORMAL);

        $this->assertEquals(Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_COERCE_TYPES, $f->getConfig());
    }

    public function testCheckModeWhenGettingFilteredConfig(): void
    {
        $f = new Factory();

        $this->assertEquals(Constraint::CHECK_MODE_NORMAL, $f->getConfig(Constraint::CHECK_MODE_NORMAL));
    }

    public function testCheckModeWhenRemovingConfig(): void
    {
        $f = new Factory();

        $f->removeConfig(Constraint::CHECK_MODE_COERCE_TYPES);

        $this->assertEquals(Constraint::CHECK_MODE_NORMAL, $f->getConfig());
    }

    public function testCheckModeWhenResettingToDefault(): void
    {
        $f = new Factory();

        $f->setConfig(Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_TYPE_CAST);
        $f->setConfig();
        $this->assertEquals(Constraint::CHECK_MODE_NORMAL, $f->getConfig());
    }
}
