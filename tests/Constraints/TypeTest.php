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
     */
    public function provideIndefiniteArticlesForTypes(): array
    {
        return [
            ['integer', 'an integer'],
            ['number', 'a number'],
            ['boolean', 'a boolean'],
            ['object', 'an object'],
            ['array', 'an array'],
            ['string', 'a string'],
            ['null', 'a null', [], 'array'],
            [['string', 'boolean', 'integer'], 'a string, a boolean or an integer'],
            [['string', 'boolean'], 'a string or a boolean'],
            [['string'], 'a string'],
        ];
    }

    /**
     * @dataProvider provideIndefiniteArticlesForTypes
     */
    public function testIndefiniteArticleForTypeInTypeCheckErrorMessage($type, $wording, $value = null, $label = 'NULL'): void
    {
        $constraint = new TypeConstraint();
        $constraint->check($value, (object) ['type' => $type]);
        $this->assertTypeConstraintError(ucwords($label) . " value found, but $wording is required", $constraint);
    }

    /**
     * Test uncovered areas of the loose type checker
     */
    public function testLooseTypeChecking(): void
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
    private function assertTypeConstraintError($expected, TypeConstraint $actual): void
    {
        $actualErrors = $actual->getErrors();

        $this->assertCount(1, $actualErrors, 'Failed to assert that Type has exactly one error to assert the error message against.');

        $actualError = $actualErrors[0];

        $this->assertIsArray($actualError, sprintf('Failed to assert that Type error is an array, %s given', gettype($actualError)));

        $messageKey = 'message';
        $this->assertArrayHasKey(
            $messageKey, $actualError,
            sprintf('Failed to assert that Type error has a message key %s.', var_export($messageKey, true))
        );

        $actualMessage = $actualError[$messageKey];

        $this->assertEquals($expected, $actualMessage); // first equal for the diff
        $this->assertSame($expected, $actualMessage); // the same for the strictness
    }

    public function validNameWordingDataProvider(): array
    {
        $wordings = [];

        foreach (array_keys(TypeConstraint::$wording) as $value) {
            $wordings[] = [$value];
        }

        return $wordings;
    }

    /**
     * @dataProvider validNameWordingDataProvider
     */
    public function testValidateTypeNameWording($nameWording): void
    {
        $t = new TypeConstraint();
        $r = new \ReflectionObject($t);
        $m = $r->getMethod('validateTypeNameWording');
        $m->setAccessible(true);

        $m->invoke($t, $nameWording);
        $this->expectNotToPerformAssertions();
    }

    public function testInvalidateTypeNameWording(): void
    {
        $t = new TypeConstraint();
        $r = new \ReflectionObject($t);
        $m = $r->getMethod('validateTypeNameWording');
        $m->setAccessible(true);

        $this->expectException('\UnexpectedValueException');
        $this->expectExceptionMessage("No wording for 'notAValidTypeName' available, expected wordings are: [an integer, a number, a boolean, an object, an array, a string, a null]");

        $m->invoke($t, 'notAValidTypeName');
    }

    public function testValidateTypeException(): void
    {
        $t = new TypeConstraint();
        $data = new \stdClass();
        $schema = json_decode('{"type": "notAValidTypeName"}');

        $this->expectException('JsonSchema\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('object is an invalid type for notAValidTypeName');

        $t->check($data, $schema);
    }
}
