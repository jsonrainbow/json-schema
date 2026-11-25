<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class FailFastTest extends TestCase
{
    /**
     * The branches of oneOf, anyOf, not and if are evaluated up to their first error, as only their validity is
     * needed. All errors must still be reported, as before.
     *
     * @dataProvider provideCases
     *
     * @param list<array{string, string}> $expectedErrors pointer and constraint name of each error
     */
    public function testAllErrorsAreReportedByDefault(string $dialect, string $schema, string $data, array $expectedErrors): void
    {
        $errors = $this->validate($dialect, $schema, $data, Constraint::CHECK_MODE_STRICT);

        self::assertSame($expectedErrors, $errors);
    }

    /**
     * @dataProvider provideCases
     *
     * @param list<array{string, string}> $expectedErrors
     */
    public function testFailFastReportsOnlyTheFirstError(string $dialect, string $schema, string $data, array $expectedErrors): void
    {
        $errors = $this->validate($dialect, $schema, $data, Constraint::CHECK_MODE_STRICT | Constraint::CHECK_MODE_FAIL_FAST);

        self::assertCount(1, $errors);
    }

    /**
     * @dataProvider provideDialects
     */
    public function testFailFastReportsNoErrorForAValidValue(string $dialect): void
    {
        $schema = '{"properties": {"p": {"oneOf": [{"type": "integer"}, {"items": {"anyOf": [{"type": "string"}, {"type": "boolean"}]}}]}}}';

        $errors = $this->validate($dialect, $schema, '{"p": ["x", true]}', Constraint::CHECK_MODE_STRICT | Constraint::CHECK_MODE_FAIL_FAST);

        self::assertSame([], $errors);
    }

    /**
     * @return \Generator<string, array{string, string, string, list<array{string, string}>}>
     */
    public static function provideCases(): \Generator
    {
        $cases = [
            'oneOf without a match' => [
                '{"oneOf": [{"type": "string"}, {"properties": {"a": {"type": "integer"}, "b": {"type": "integer"}}}]}',
                '{"a": "x", "b": "y"}',
                [['', 'type'], ['/a', 'type'], ['/b', 'type'], ['', 'oneOf']],
            ],
            'oneOf with two matches' => [
                '{"oneOf": [{"type": "object"}, {"properties": {"a": {"type": "string"}}}]}',
                '{"a": "x"}',
                [['', 'oneOf']],
            ],
            'anyOf without a match' => [
                '{"anyOf": [{"type": "string"}, {"items": {"type": "integer"}}]}',
                '["x", "y"]',
                [['', 'type'], ['/0', 'type'], ['/1', 'type'], ['', 'anyOf']],
            ],
            'not' => [
                '{"not": {"properties": {"a": {"type": "integer"}, "b": {"type": "integer"}}}}',
                '{"a": 1, "b": 2}',
                [['', 'not']],
            ],
            'nested oneOf' => [
                '{"properties": {"p": {"oneOf": [{"type": "integer"}, {"items": {"oneOf": [{"type": "string"}, {"type": "boolean"}]}}]}}}',
                '{"p": [1, "x", 2]}',
                [
                    ['/p', 'type'],
                    ['/p/0', 'type'], ['/p/0', 'type'], ['/p/0', 'oneOf'],
                    ['/p/2', 'type'], ['/p/2', 'type'], ['/p/2', 'oneOf'],
                    ['/p', 'oneOf'],
                ],
            ],
        ];

        foreach (self::provideDialects() as $dialectName => [$dialect]) {
            foreach ($cases as $caseName => [$schema, $data, $expectedErrors]) {
                yield $dialectName . ': ' . $caseName => [$dialect, $schema, $data, $expectedErrors];
            }
        }

        foreach (['Draft-07' => DraftIdentifiers::DRAFT_7, 'Draft 2019-09' => DraftIdentifiers::DRAFT_2019_09] as $dialectName => $dialect) {
            yield $dialectName . ': if' => [
                $dialect,
                '{"if": {"properties": {"a": {"type": "integer"}, "b": {"type": "integer"}}}, "then": {"required": ["c"]}, "else": {"required": ["d"]}}',
                '{"a": "x", "b": "y"}',
                [['/d', 'required']],
            ];
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideDialects(): array
    {
        return [
            'Draft-06' => [DraftIdentifiers::DRAFT_6],
            'Draft-07' => [DraftIdentifiers::DRAFT_7],
            'Draft 2019-09' => [DraftIdentifiers::DRAFT_2019_09],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    private function validate(string $dialect, string $schema, string $data, int $checkMode): array
    {
        $decodedSchema = json_decode($schema);
        $decodedSchema->{'$schema'} = $dialect;
        $decodedData = json_decode($data);

        $validator = new Validator();
        $validator->validate($decodedData, $decodedSchema, $checkMode);

        return array_map(static function (array $error): array {
            return [$error['pointer'], $error['constraint']['name']];
        }, $validator->getErrors());
    }
}
