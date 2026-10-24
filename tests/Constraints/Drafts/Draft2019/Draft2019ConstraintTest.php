<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Drafts\Draft2019;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Drafts\Draft2019\Draft2019Constraint;
use JsonSchema\Constraints\Factory;
use PHPUnit\Framework\TestCase;

class Draft2019ConstraintTest extends TestCase
{
    public function testNoConstraintsAreInstantiatedForAnEmptySchema(): void
    {
        $constraint = new Draft2019Constraint();
        $calledKeywords = [];
        $this->injectFactory($constraint, $this->createSpyFactory($calledKeywords));

        $schema = json_decode('{}');
        $value = json_decode('{}');
        $constraint->check($value, $schema);

        $this->assertSame([], $calledKeywords);
    }

    public function testOnlyKeywordsPresentInSchemaAreInstantiated(): void
    {
        $constraint = new Draft2019Constraint();
        $calledKeywords = [];
        $this->injectFactory($constraint, $this->createSpyFactory($calledKeywords));

        $schema = json_decode('{"type": "object", "required": ["id"]}');
        $value = json_decode('{"id": 1}');
        $constraint->check($value, $schema);

        sort($calledKeywords);
        $this->assertSame(['required', 'type'], $calledKeywords);
    }

    public function testRefKeywordIsInstantiatedWhenDollarRefPropertyIsPresent(): void
    {
        $constraint = new Draft2019Constraint();
        $calledKeywords = [];
        $this->injectFactory($constraint, $this->createSpyFactory($calledKeywords));

        $schema = json_decode('{"$ref": "#/definitions/foo"}');
        $value = json_decode('{}');
        $constraint->check($value, $schema);

        $this->assertSame(['ref'], $calledKeywords);
    }

    public function testIfThenElseKeywordIsInstantiatedWhenIfPropertyIsPresent(): void
    {
        $constraint = new Draft2019Constraint();
        $calledKeywords = [];
        $this->injectFactory($constraint, $this->createSpyFactory($calledKeywords));

        $schema = json_decode('{"if": {"type": "string"}}');
        $value = json_decode('"hello"');
        $constraint->check($value, $schema);

        $this->assertSame(['ifThenElse'], $calledKeywords);
    }

    /**
     * @dataProvider contentPropertyProvider
     */
    public function testContentKeywordIsInstantiatedWhenEitherContentPropertyIsPresent(string $property): void
    {
        $constraint = new Draft2019Constraint();
        $calledKeywords = [];
        $this->injectFactory($constraint, $this->createSpyFactory($calledKeywords));

        $schema = json_decode(sprintf('{"%s": "text/plain"}', $property));
        $value = json_decode('"hello"');
        $constraint->check($value, $schema);

        $this->assertSame(['content'], $calledKeywords);
    }

    public static function contentPropertyProvider(): \Generator
    {
        yield 'contentMediaType' => ['contentMediaType'];
        yield 'contentEncoding' => ['contentEncoding'];
    }

    public function testDependentSchemasAndDependentRequiredAreInstantiatedIndependently(): void
    {
        $constraint = new Draft2019Constraint();
        $calledKeywords = [];
        $this->injectFactory($constraint, $this->createSpyFactory($calledKeywords));

        $schema = json_decode('{"dependentRequired": {"a": ["b"]}}');
        $value = json_decode('{"a": 1, "b": 2}');
        $constraint->check($value, $schema);

        $this->assertSame(['dependentRequired'], $calledKeywords);
    }

    private function createSpyFactory(array &$calledKeywords): Factory
    {
        $constraintStub = $this->createMock(ConstraintInterface::class);
        $constraintStub->method('getErrors')->willReturn([]);

        $factory = $this->createMock(Factory::class);
        $factory->method('createInstanceFor')
            ->willReturnCallback(static function (string $keyword) use (&$calledKeywords, $constraintStub) {
                $calledKeywords[] = $keyword;

                return $constraintStub;
            });

        return $factory;
    }

    private function injectFactory(Draft2019Constraint $constraint, Factory $factory): void
    {
        $property = new \ReflectionProperty(BaseConstraint::class, 'factory');
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
        $property->setValue($constraint, $factory);
    }
}
