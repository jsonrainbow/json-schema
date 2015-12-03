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
     * @return array
     */
    public function constraintNameProvider()
    {
        return array(
            array('exists', 'JsonSchema\Constraints\StringConstraint'),
            array('custom', 'JsonSchema\Tests\Constraints\Fixtures\CustomConstraint'),
        );
    }

    /**
     * @dataProvider constraintNameProvider
     *
     * @param string $constraintName
     * @param string $className
     */
    public function testConstraintInstanceWithoutCtrParams($constraintName, $className)
    {
        $factory = new Factory();

        // NOTE the uriRetriever and factory will be new instances; this is due to the Constraint class..
        $factory->addConstraint($constraintName, new $className());

        $constraint = $factory->createInstanceFor($constraintName);

        $this->assertInstanceOf($className, $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);

        $this->assertNotSame($factory->getUriRetriever(), $constraint->getUriRetriever());
    }

    /**
     * @dataProvider constraintNameProvider
     *
     * @param string $constraintName
     * @param string $className
     */
    public function testConstraintInstanceWithCtrParams($constraintName, $className)
    {
        $factory = new Factory();
        $factory->addConstraint($constraintName,
            new $className(Constraint::CHECK_MODE_NORMAL, $factory->getUriRetriever(), $factory));
        $constraint = $factory->createInstanceFor($constraintName);

        $this->assertInstanceOf($className, $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
        $this->assertSame($factory->getUriRetriever(), $constraint->getUriRetriever());
        $this->assertSame($factory, $constraint->getFactory());
    }

    /**
     * @dataProvider constraintNameProvider
     *
     * @param string $constraintName
     * @param string $className
     */
    public function testConstraintClassNameStringInjectingCtrParamsHasSame($constraintName, $className)
    {
        $factory = new Factory();
        $factory->addConstraint($constraintName, $className);
        $constraint = $factory->createInstanceFor($constraintName);

        $this->assertInstanceOf($className, $constraint);
        $this->assertInstanceOf('JsonSchema\Constraints\ConstraintInterface', $constraint);
        $this->assertSame($factory->getUriRetriever(), $constraint->getUriRetriever());
        $this->assertSame($factory, $constraint->getFactory());
    }

    /**
     * @todo possible own exception?
     * @expectedException \JsonSchema\Exception\InvalidArgumentException
     */
    public function testConstraintClassNameStringIsNotAClass()
    {
        $name = 'NotAClass';

        $factory = new Factory();
        $factory->addConstraint($name, $name);
        $factory->createInstanceFor($name);
    }

    /**
     * @todo possible own exception?
     * @expectedException \JsonSchema\Exception\InvalidArgumentException
     */
    public function testConstraintClassNameStringIsAClassButNotAConstraint()
    {
        $name = 'JsonSchema\RefResolver'; // Class but not ConstraintInterface

        $factory = new Factory();
        $factory->addConstraint($name, $name);
        $factory->createInstanceFor($name);
    }

    /**
     *
     */
    public function testConstraintCallable()
    {
        $factory = new Factory();
        $factory->addConstraint('callable', function () {});
        $this->assertInstanceOf('JsonSchema\Constraints\CallableConstraint', $factory->createInstanceFor('callable'));
    }

}
