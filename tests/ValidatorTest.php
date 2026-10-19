<?php

declare(strict_types=1);

namespace JsonSchema\Tests;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Exception\ValidationException;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidateWithAssocSchema(): void
    {
        $schema = json_decode('{"properties":{"propertyOne":{"type":"array","items":[{"type":"string"}]}}}', true);
        $data = json_decode('{"propertyOne":[42]}', true);

        $validator = new Validator();
        $validator->validate($data, $schema);

        $this->assertFalse($validator->isValid(), 'Validation succeeded, but should have failed.');
    }

    public function testValidateWithAssocSchemaWithRelativeRefs(): void
    {
        $schema = json_decode(file_get_contents(__DIR__ . '/fixtures/relative.json'), true);
        $data = json_decode('{"foo":{"foo": "bar"}}', false);

        $validator = new Validator();
        $validator->validate($data, $schema);

        $this->assertTrue($validator->isValid(), 'Validation failed, but should have succeeded.');
    }

    public function testBadAssocSchemaInput(): void
    {
        $schema = ['propertyOne' => fopen('php://stdout', 'wb')];
        $data = json_decode('{"propertyOne":[42]}', true);

        $validator = new Validator();

        $this->expectException(InvalidArgumentException::class);
        $validator->validate($data, $schema);
    }

    public function testDeprecatedCheckDelegatesToValidate(): void
    {
        $schema = json_decode('{"type":"string"}');
        $data = json_decode('42');

        $validator = new Validator();
        $validator->check($data, $schema);

        $this->assertFalse($validator->isValid(), 'Validation succeeded, but should have failed.');
    }

    public function testDeprecatedCoerceDelegatesToValidate(): void
    {
        $schema = json_decode('{"type":"integer"}');
        $data = json_decode('"42"');

        $validator = new Validator();
        $validator->coerce($data, $schema);

        $this->assertTrue($validator->isValid(), 'Validation failed, but should have succeeded.');
    }

    /** @dataProvider draftsWithCheckModeChangedBetweenValidations */
    public function testDraftConstraintsUseTheCheckModeOfEachValidation(string $schemaUri, int $firstCheckMode, int $secondCheckMode, bool $expectedValid): void
    {
        $schema = (object) ['$schema' => $schemaUri, 'format' => 'date-time'];
        $validator = new Validator();

        $data = 'not-a-date';
        $validator->validate($data, $schema, $firstCheckMode);
        $data = 'not-a-date';
        $validator->validate($data, $schema, $secondCheckMode);

        $this->assertSame($expectedValid, $validator->isValid());
    }

    public function draftsWithCheckModeChangedBetweenValidations(): \Generator
    {
        $checked = Constraint::CHECK_MODE_STRICT;
        $disabled = Constraint::CHECK_MODE_STRICT | Constraint::CHECK_MODE_DISABLE_FORMAT;

        foreach (self::strictModeSchemaUris() as $draft => $schemaUri) {
            yield $draft . ', format checked after being disabled' => [$schemaUri, $disabled, $checked, false];
            yield $draft . ', format disabled after being checked' => [$schemaUri, $checked, $disabled, true];
        }
    }

    /** @dataProvider draftsWithExceptionsEnabledBetweenValidations */
    public function testDraftConstraintsThrowWhenExceptionsAreEnabledForALaterValidation(string $schemaUri): void
    {
        $schema = (object) ['$schema' => $schemaUri, 'type' => 'integer'];
        $validator = new Validator();

        $data = 'foo';
        $validator->validate($data, $schema, Constraint::CHECK_MODE_STRICT);
        $this->assertFalse($validator->isValid());

        $this->expectException(ValidationException::class);

        $data = 'foo';
        $validator->validate($data, $schema, Constraint::CHECK_MODE_STRICT | Constraint::CHECK_MODE_EXCEPTIONS);
    }

    public function draftsWithExceptionsEnabledBetweenValidations(): \Generator
    {
        foreach (self::strictModeSchemaUris() as $draft => $schemaUri) {
            yield $draft => [$schemaUri];
        }
    }

    /**
     * @return array<string, string>
     */
    private static function strictModeSchemaUris(): array
    {
        return [
            'draft-06' => 'http://json-schema.org/draft-06/schema#',
            'draft-07' => 'http://json-schema.org/draft-07/schema#',
            'draft-2019-09' => 'https://json-schema.org/draft/2019-09/schema',
        ];
    }

    /** @dataProvider draftIdentifiersNotSupportedForStrictMode */
    public function testItThrowsForStrictValidationOnNonSupportingDraft(DraftIdentifiers $draft): void
    {
        $data = json_decode('"42"', false);
        $schema = json_decode('{"type":"integer"}', false);
        $factory = new Factory(null, null, Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_STRICT);
        $factory->setDefaultDialect($draft->getValue());
        $validator = new Validator($factory);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown constraint ' . $draft->toConstraintName());

        $validator->validate($data, $schema);
    }

    public function draftIdentifiersNotSupportedForStrictMode(): \Generator
    {
        foreach (DraftIdentifiers::getEnumerators() as $draft) {
            switch ($draft) {
                case DraftIdentifiers::DRAFT_6():
                case DraftIdentifiers::DRAFT_7():
                case DraftIdentifiers::DRAFT_2019_09():
                    break;
                default:
                    yield $draft->toConstraintName() => [$draft];
            }
        }
    }
}
