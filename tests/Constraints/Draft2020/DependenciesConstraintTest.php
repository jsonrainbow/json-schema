<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft2020;

use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;
use JsonSchema\Validator;

class DependenciesConstraintTest extends VeryBaseTestCase
{
    /**
     * @dataProvider provideCases
     */
    public function testDependenciesAreValidatedForCompatibility(string $schema, string $data, bool $expectedToBeValid): void
    {
        $decodedSchema = json_decode($schema);
        $decodedSchema->{'$schema'} = DraftIdentifiers::DRAFT_2020_12;
        $decodedData = json_decode($data);

        $validator = new Validator();
        $validator->validate($decodedData, $decodedSchema, Constraint::CHECK_MODE_STRICT);

        self::assertSame($expectedToBeValid, $validator->isValid(), (string) json_encode($validator->getErrors()));
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function provideCases(): array
    {
        return [
            'a present dependency is valid' => ['{"dependencies": {"bar": ["foo"]}}', '{"foo": 1, "bar": 2}', true],
            'a missing dependency is invalid' => ['{"dependencies": {"bar": ["foo"]}}', '{"bar": 2}', false],
            'a missing dependant is valid' => ['{"dependencies": {"bar": ["foo"]}}', '{"foo": 1}', true],
            'a dependant matching the dependent schema is valid' => ['{"dependencies": {"bar": {"properties": {"foo": {"type": "integer"}}}}}', '{"foo": 1, "bar": 2}', true],
            'a dependant not matching the dependent schema is invalid' => ['{"dependencies": {"bar": {"properties": {"foo": {"type": "integer"}}}}}', '{"foo": "quux", "bar": 2}', false],
            'a dependant with a false dependent schema is invalid' => ['{"dependencies": {"bar": false}}', '{"bar": 2}', false],
        ];
    }
}
