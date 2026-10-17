<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class PropertyNamesTest extends TestCase
{
    /**
     * @dataProvider numericPropertyNameProvider
     */
    public function testNumericPropertyNamesAreValidatedAsStrings(string $draft, string $propertyNames, string $input, bool $expectedValid): void
    {
        $schema = json_decode(sprintf('{"$schema": "%s", "propertyNames": %s}', $draft, $propertyNames), false);
        $value = json_decode($input, false);

        $validator = new Validator();
        $validator->validate($value, $schema, Constraint::CHECK_MODE_STRICT);

        self::assertSame($expectedValid, $validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider draftProvider
     */
    public function testAnySchemaIsAppliedToThePropertyNames(string $draft): void
    {
        $propertyNames = '{"minLength": 2, "format": "email", "not": {"const": "x@y.cz"}, "allOf": [{"$ref": "#/definitions/lowercase"}]}';
        $definitions = '{"lowercase": {"pattern": "^[a-z@.]+$"}}';

        self::assertSame([], $this->validate($draft, $propertyNames, '{"ab@cd.cz": true}', $definitions));
        self::assertSame(
            [
                ['a', 'minLength'],
                ['a', 'emailFormat'],
                ['x@y.cz', 'not'],
                ['AB@cd.cz', 'allOf'],
                ['AB@cd.cz', 'pattern'],
            ],
            array_map(static function (array $error): array {
                return [$error['constraint']['params']['name'], $error['constraint']['params']['violating']];
            }, $this->validate($draft, $propertyNames, '{"a": true, "x@y.cz": true, "AB@cd.cz": true}', $definitions))
        );
    }

    /**
     * @dataProvider draftProvider
     */
    public function testErrorMessageNamesThePropertyName(string $draft): void
    {
        $errors = $this->validate($draft, '{"maxLength": 3}', '{"toolong": true}');

        self::assertCount(1, $errors);
        self::assertSame('Property name toolong is invalid', $errors[0]['message']);
        self::assertSame('propertyNames', $errors[0]['constraint']['name']);
        self::assertSame('toolong', $errors[0]['constraint']['params']['name']);
        self::assertSame('maxLength', $errors[0]['constraint']['params']['violating']);
    }

    public function draftProvider(): \Generator
    {
        yield 'Draft 6' => [DraftIdentifiers::DRAFT_6];
        yield 'Draft 7' => [DraftIdentifiers::DRAFT_7];
        yield 'Draft 2019-09' => [DraftIdentifiers::DRAFT_2019_09];
    }

    public function numericPropertyNameProvider(): \Generator
    {
        $drafts = [
            'Draft 6' => DraftIdentifiers::DRAFT_6,
            'Draft 7' => DraftIdentifiers::DRAFT_7,
            'Draft 2019-09' => DraftIdentifiers::DRAFT_2019_09,
        ];
        $cases = [
            'false' => ['false', '{"1": true}', false],
            'maxLength, valid' => ['{"maxLength": 3}', '{"123": true}', true],
            'maxLength, invalid' => ['{"maxLength": 3}', '{"1234": true}', false],
            'pattern, valid' => ['{"pattern": "^[0-9]+$"}', '{"42": true}', true],
            'pattern, invalid' => ['{"pattern": "^[a-z]+$"}', '{"42": true}', false],
            'const, valid' => ['{"const": "1"}', '{"1": true}', true],
            'const, invalid' => ['{"const": "1"}', '{"2": true}', false],
            'enum, valid' => ['{"enum": ["1", "2"]}', '{"1": true, "2": true}', true],
            'enum, invalid' => ['{"enum": ["1", "2"]}', '{"3": true}', false],
        ];

        foreach ($drafts as $draftName => $draft) {
            foreach ($cases as $caseName => [$propertyNames, $input, $expectedValid]) {
                yield sprintf('%s: %s', $draftName, $caseName) => [$draft, $propertyNames, $input, $expectedValid];
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function validate(string $draft, string $propertyNames, string $input, string $definitions = '{}'): array
    {
        $schema = json_decode(sprintf('{"$schema": "%s", "propertyNames": %s, "definitions": %s}', $draft, $propertyNames, $definitions), false);
        $value = json_decode($input, false);

        $validator = new Validator();
        $validator->validate($value, $schema, Constraint::CHECK_MODE_STRICT);

        return $validator->getErrors();
    }
}
