<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use PHPUnit_Framework_TestCase as TestCase;
use JsonSchema\Constraints\Factory;
use JsonSchema\Constraints\Constraint;

class CustomConstraintTest extends TestCase
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
    public function testConstraintInstanceWithoutCtrParams($constraintName, $expectedClass)
    {
        $this->factory->addConstraint($constraintName, new $expectedClass());
        $constraint = $this->factory->createInstanceFor($constraintName);

        $this->assertInstanceOf($expectedClass, $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
        $this->assertNotSame($this->factory->getUriRetriever(), $constraint->getUriRetriever());
    }

    /**
     * @dataProvider constraintNameProvider
     *
     * @param string $constraintName
     * @param string $expectedClass
     */
    public function testConstraintInstanceWithCtrParams($constraintName, $expectedClass)
    {
        $this->factory->addConstraint($constraintName,
            new $expectedClass(Constraint::CHECK_MODE_NORMAL, $this->factory->getUriRetriever(), $this->factory));
        $constraint = $this->factory->createInstanceFor($constraintName);

        $this->assertInstanceOf($expectedClass, $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
        $this->assertSame($this->factory->getUriRetriever(), $constraint->getUriRetriever());
        $this->assertSame($this->factory, $constraint->getFactory());
    }

    /**
     * @return array
     */
    public function constraintNameProvider()
    {
        return array(
            array('exists', 'JsonSchema\Constraints\StringConstraint'),
            array('custom', 'JsonSchema\Tests\Constraints\Fixtures\CustomConstraint'),
        );
    }

}
