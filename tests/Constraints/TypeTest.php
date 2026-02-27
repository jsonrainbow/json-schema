<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\TypeCheck\LooseTypeCheck;
use JsonSchema\Constraints\TypeConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Class TypeTest
 *
 * @package JsonSchema\Tests\Constraints
 *
 * @author hakre <https://github.com/hakre>
 */
class TypeTest extends TestCase
{
    /**
     * @see testIndefiniteArticleForTypeInTypeCheckErrorMessage
     *
     * @return array
     */
    public function provideIndefiniteArticlesForTypes()
    {
        return array(
            array('integer', 'an integer'),
            array('number', 'a number'),
            array('boolean', 'a boolean'),
            array('object', 'an object'),
            array('array', 'an array'),
            array('string', 'a string'),
            array('null', 'a null', array(), 'array'),
            array(array('string', 'boolean', 'integer'), 'a string, a boolean or an integer'),
            array(array('string', 'boolean'), 'a string or a boolean'),
            array(array('string'), 'a string'),
        );
    }

    /**
     * @dataProvider provideIndefiniteArticlesForTypes
     */
    public function testIndefiniteArticleForTypeInTypeCheckErrorMessage($type, $wording, $value = null, $label = 'NULL')
    {
        $constraint = new TypeConstraint();
        $constraint->check($value, (object) array('type' => $type));
        $this->assertTypeConstraintError(ucwords($label) . " value found, but $wording is required", $constraint);
    }

    /**
     * Test uncovered areas of the loose type checker
     */
    public function testLooseTypeChecking()
    {
        $v = new \stdClass();
        $v->property = 'dataOne';
        LooseTypeCheck::propertySet($v, 'property', 'dataTwo');
        $this->assertEquals('dataTwo', $v->property);
        $this->assertEquals('dataTwo', LooseTypeCheck::propertyGet($v, 'property'));
        $this->assertEquals(1, LooseTypeCheck::propertyCount($v));
    }

    /**
     * Helper to assert an error message
     *
     * @param string         $expected
     * @param TypeConstraint $actual
     */
    private function assertTypeConstraintError($expected, TypeConstraint $actual)
    {
        $actualErrors = $actual->getErrors();

        $this->assertCount(1, $actualErrors, 'Failed to assert that Type has exactly one error to assert the error message against.');

        $actualError = $actualErrors[0];

        $this->assertInternalType('array', $actualError, sprintf('Failed to assert that Type error is an array, %s given', gettype($actualError)));

        $messageKey = 'message';
        $this->assertArrayHasKey(
            $messageKey, $actualError,
            sprintf('Failed to assert that Type error has a message key %s.', var_export($messageKey, true))
        );

        $actualMessage = $actualError[$messageKey];

        $this->assertEquals($expected, $actualMessage); // first equal for the diff
        $this->assertSame($expected, $actualMessage); // the same for the strictness
    }

    public function validNameWordingDataProvider()
    {
        $wordings = array();

        foreach (array_keys(TypeConstraint::$wording) as $value) {
            $wordings[] = array($value);
        }

        return $wordings;
    }

    /**
     * @dataProvider validNameWordingDataProvider
     */
    public function testValidateTypeNameWording($nameWording)
    {
        $t = new TypeConstraint();
        $r = new \ReflectionObject($t);
        $m = $r->getMethod('validateTypeNameWording');
        $m->setAccessible(true);

        $m->invoke($t, $nameWording);
    }

    public function testInvalidateTypeNameWording()
    {
        $t = new TypeConstraint();
        $r = new \ReflectionObject($t);
        $m = $r->getMethod('validateTypeNameWording');
        $m->setAccessible(true);

        $this->setExpectedException(
            '\UnexpectedValueException',
            "No wording for 'notAValidTypeName' available, expected wordings are: [an integer, a number, a boolean, an object, an array, a string, a null]"
        );
        $m->invoke($t, 'notAValidTypeName');
    }

    public function testValidateTypeException()
    {
        $t = new TypeConstraint();
        $data = new \stdClass();
        $schema = json_decode('{"type": "notAValidTypeName"}');

        $this->setExpectedException(
            'JsonSchema\Exception\InvalidArgumentException',
            'object is an invalid type for notAValidTypeName'
        );
        $t->check($data, $schema);
    }
}
