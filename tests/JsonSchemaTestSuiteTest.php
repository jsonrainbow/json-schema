<?php

declare(strict_types=1);

namespace JsonSchema\Tests;

use CallbackFilterIterator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\DraftIdentifiers;
use JsonSchema\SchemaStorage;
use JsonSchema\SchemaStorageInterface;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class JsonSchemaTestSuiteTest extends TestCase
{
    /**
     * @dataProvider casesDataProvider
     *
     * @param \stdClass|bool $schema
     * @param mixed          $data
     */
    public function testTestCaseValidatesCorrectly(
        string $testCaseDescription,
        string $testDescription,
        $schema,
        $data,
        int $checkMode,
        DraftIdentifiers $draft,
        bool $expectedValidationResult,
        bool $optional
    ): void {
        $schemaStorage = new SchemaStorage();
        $id = is_object($schema) && property_exists($schema, 'id') ? $schema->id : SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        $schemaStorage->addSchema($id, $schema);
        $this->loadRemotesIntoStorage($schemaStorage);
        $factory = new Factory($schemaStorage);
        $factory->setDefaultDialect($draft->getValue());
        $validator = new Validator($factory);

        try {
            $validator->validate($data, $schema, $checkMode);
        } catch (\Exception $e) {
            if ($optional) {
                $this->markTestSkipped('Optional test case throws exception during validate() invocation: "' . $e->getMessage() . '"');
            }

            throw $e;
        }

        if ($optional && $expectedValidationResult !== (count($validator->getErrors()) === 0)) {
            $this->markTestSkipped('Optional test case would fail');
        }

        self::assertEquals(
            $expectedValidationResult,
            count($validator->getErrors()) === 0,
            $expectedValidationResult ? print_r($validator->getErrors(), true) : 'Validator returned valid but the testcase indicates it is invalid'
        );
    }

    public function casesDataProvider(): \Generator
    {
        $testDir = __DIR__ . '/../vendor/json-schema/json-schema-test-suite/tests';
        $drafts = array_filter(glob($testDir . '/*'), static function (string $filename) {
            return is_dir($filename);
        });
        $skippedDrafts = ['draft2020-12', 'draft-next', 'latest'];

        foreach ($drafts as $draft) {
            $baseDraftName = basename($draft);
            if (in_array($baseDraftName, $skippedDrafts, true)) {
                continue;
            }

            $files = new CallbackFilterIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($draft)
                ),
                function ($file) {
                    return $file->isFile() && strtolower($file->getExtension()) === 'json';
                }
            );
            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                $contents = json_decode(file_get_contents($file->getPathname()), false);
                foreach ($contents as $testCase) {
                    foreach ($testCase->tests as $test) {
                        [,$filename] = explode('/tests/', $file->getRealPath(), 2);
                        $name = sprintf(
                            '[%s]: %s: %s is expected to be %s',
                            $filename,
                            $testCase->description,
                            $test->description,
                            $test->valid ? 'valid' : 'invalid'
                        );

                        if ($this->shouldNotYieldTest($name)) {
                            continue;
                        }

                        yield $name => [
                            'testCaseDescription' => $testCase->description,
                            'testDescription' => $test->description,
                            'schema' => $testCase->schema,
                            'data' => $test->data,
                            'checkMode' => $this->getCheckModeForDraft($baseDraftName),
                            'draft' => DraftIdentifiers::fromConstraintName($baseDraftName),
                            'expectedValidationResult' => $test->valid,
                            'optional' => str_contains($file->getPathname(), '/optional/')
                        ];
                    }
                }
            }
        }
    }

    private function loadRemotesIntoStorage(SchemaStorageInterface $storage): void
    {
        $remotesDir = __DIR__ . '/../vendor/json-schema/json-schema-test-suite/remotes';

        $directory = new \RecursiveDirectoryIterator($remotesDir);
        $iterator = new \RecursiveIteratorIterator($directory);

        foreach ($iterator as $info) {
            if (!$info->isFile()) {
                continue;
            }

            $id = str_replace($remotesDir, 'http://localhost:1234', $info->getPathname());
            $storage->addSchema($id, json_decode(file_get_contents($info->getPathname()), false));
        }
    }

    private function shouldNotYieldTest(string $name): bool
    {
        $skip = [
            '[draft4/ref.json]: refs with quote: object with numbers is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: refs with quote: object with strings is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: Location-independent identifier: match is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: Location-independent identifier: mismatch is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: Location-independent identifier with base URI change in subschema: match is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: Location-independent identifier with base URI change in subschema: mismatch is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: id must be resolved against nearest parent, not just immediate parent: number is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: id must be resolved against nearest parent, not just immediate parent: non-number is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: empty tokens in $ref json-pointer: number is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: empty tokens in $ref json-pointer: non-number is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/refRemote.json]: base URI change - change folder: number is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/refRemote.json]: base URI change - change folder: string is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/refRemote.json]: Location-independent identifier in remote ref: integer is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/refRemote.json]: Location-independent identifier in remote ref: string is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft6/ref.json]: Location-independent identifier with base URI change in subschema: mismatch is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft6/ref.json]: Location-independent identifier: mismatch is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            '[draft6/ref.json]: refs with quote: object with strings is invalid is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            '[draft6/ref.json]: empty tokens in $ref json-pointer: non-number is invalid is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            '[draft6/refRemote.json]: base URI change - change folder: string is invalid is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            '[draft6/refRemote.json]: Location-independent identifier in remote ref: string is invalid is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            // Skipping complex edge cases for now
            '[draft6/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it is expected to be valid',
            '[draft6/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf is expected to be invalid',
            '[draft6/refRemote.json]: $ref to $ref finds location-independent $id: non-number is invalid is expected to be invalid',
            '[draft6/ref.json]: ref overrides any sibling keywords: ref valid, maxItems ignored is expected to be valid',
            '[draft6/ref.json]: Reference an anchor with a non-relative URI: mismatch is expected to be invalid',
            '[draft6/ref.json]: refs with relative uris and defs: invalid on inner field is expected to be invalid',
            '[draft6/ref.json]: refs with relative uris and defs: invalid on outer field is expected to be invalid',
            '[draft6/ref.json]: relative refs with absolute uris and defs: invalid on inner field is expected to be invalid',
            '[draft6/ref.json]: relative refs with absolute uris and defs: invalid on outer field is expected to be invalid',
            '[draft6/ref.json]: simple URN base URI with JSON pointer: a non-string is invalid is expected to be invalid',
            '[draft6/ref.json]: URN base URI with NSS: a non-string is invalid is expected to be invalid',
            '[draft6/ref.json]: URN base URI with r-component: a non-string is invalid is expected to be invalid',
            '[draft6/ref.json]: URN base URI with q-component: a non-string is invalid is expected to be invalid',
            '[draft6/ref.json]: URN base URI with URN and anchor ref: a non-string is invalid is expected to be invalid',
            '[draft7/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it is expected to be valid',
            '[draft7/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf is expected to be invalid',
            '[draft7/refRemote.json]: $ref to $ref finds location-independent $id: non-number is invalid is expected to be invalid',
            '[draft7/ref.json]: ref overrides any sibling keywords: ref valid, maxItems ignored is expected to be valid',
            '[draft7/ref.json]: Reference an anchor with a non-relative URI: mismatch is expected to be invalid',
            '[draft7/ref.json]: refs with relative uris and defs: invalid on inner field is expected to be invalid',
            '[draft7/ref.json]: refs with relative uris and defs: invalid on outer field is expected to be invalid',
            '[draft7/ref.json]: relative refs with absolute uris and defs: invalid on inner field is expected to be invalid',
            '[draft7/ref.json]: relative refs with absolute uris and defs: invalid on outer field is expected to be invalid',
            '[draft7/ref.json]: simple URN base URI with JSON pointer: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: URN base URI with NSS: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: URN base URI with r-component: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: URN base URI with q-component: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: URN base URI with URN and anchor ref: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: $id must be resolved against nearest parent, not just immediate parent: non-number is invalid is expected to be invalid',
            '[draft7/ref.json]: Location-independent identifier with base URI change in subschema: mismatch is expected to be invalid',
            '[draft7/ref.json]: Location-independent identifier: mismatch is expected to be invalid',
            '[draft7/refRemote.json]: base URI change - change folder: string is invalid is expected to be invalid',
            '[draft7/refRemote.json]: Location-independent identifier in remote ref: string is invalid is expected to be invalid',
            // Draft 2019-09 complex constraints which aren't supported initionaly
            '[draft2019-09/recursiveRef.json]: $recursiveRef without $recursiveAnchor works like $ref: recursive mismatch is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef without using nesting: integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef without using nesting: two levels, no match is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with $recursiveAnchor: false works like $ref: integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with $recursiveAnchor: false works like $ref: two levels, integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor works like $ref: integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor works like $ref: two levels, integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor in the initial target schema resource: leaf node doest not match: recursion uses the inner schema is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor in the outer schema resource: leaf node does not match: recursion only uses inner schema is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor in the initial target schema resource: leaf node does not match: recursion uses the inner schema is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: multiple dynamic paths to the $recursiveRef keyword: recurse to integerNode - floats are not allowed is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: dynamic $recursiveRef destination (not predictable at schema compile time): integer node is expected to be invalid',
            '[draft2019-09/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: applicator vocabulary still works is expected to be invalid',
            '[draft2019-09/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: no validation: valid number is expected to be valid',
            '[draft2019-09/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: no validation: invalid number, but it still validates is expected to be valid',
            '[draft2019-09/vocabulary.json]: ignore unrecognized optional vocabulary: string value is expected to be invalid',
            '[draft2019-09/vocabulary.json]: ignore unrecognized optional vocabulary: number value is expected to be valid',
            '[draft2019-09/defs.json]: validate definition against metaschema: invalid definition schema is expected to be invalid',
            '[draft2019-09/id.json]: Invalid use of fragments in location-independent $id: Identifier name is expected to be invalid',
            '[draft2019-09/id.json]: Invalid use of fragments in location-independent $id: Identifier name and no ref is expected to be invalid',
            '[draft2019-09/id.json]: Invalid use of fragments in location-independent $id: Identifier path is expected to be invalid',
            '[draft2019-09/id.json]: Invalid use of fragments in location-independent $id: Identifier name with absolute URI is expected to be invalid',
            '[draft2019-09/id.json]: Invalid use of fragments in location-independent $id: Identifier path with absolute URI is expected to be invalid',
            '[draft2019-09/id.json]: Invalid use of fragments in location-independent $id: Identifier name with base URI change in subschema is expected to be invalid',
            '[draft2019-09/id.json]: Invalid use of fragments in location-independent $id: Identifier path with base URI change in subschema is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties schema: with invalid unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties false: with unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with adjacent properties: with unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with adjacent patternProperties: with unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with nested properties: with additional properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with nested patternProperties: with additional properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with anyOf: when one matches and has unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with anyOf: when two match and has unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with oneOf: with unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with not: with unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with if/then/else: when if is true and has unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with if/then/else: when if is false and has unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with if/then/else, then not defined: when if is true and has no unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with if/then/else, then not defined: when if is true and has unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with if/then/else, then not defined: when if is false and has unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with if/then/else, else not defined: when if is true and has unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with if/then/else, else not defined: when if is false and has no unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with if/then/else, else not defined: when if is false and has unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with dependentSchemas: with unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with boolean schemas: with unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with $ref: with unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties can\'t see inside cousins: always fails is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties can\'t see inside cousins (reverse order): always fails is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: nested unevaluatedProperties, outer true, inner false, properties outside: with no nested unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: nested unevaluatedProperties, outer true, inner false, properties outside: with nested unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: nested unevaluatedProperties, outer true, inner false, properties inside: with nested unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: cousin unevaluatedProperties, true and false, true with properties: with no nested unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: cousin unevaluatedProperties, true and false, true with properties: with nested unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: cousin unevaluatedProperties, true and false, false with properties: with nested unevaluated properties is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: property is evaluated in an uncle schema to unevaluatedProperties: uncle keyword evaluation is not significant is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: in-place applicator siblings, allOf has unevaluated: base case: both properties present is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: in-place applicator siblings, allOf has unevaluated: in place applicator siblings, foo is missing is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: in-place applicator siblings, anyOf has unevaluated: base case: both properties present is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: in-place applicator siblings, anyOf has unevaluated: in place applicator siblings, bar is missing is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties + single cyclic ref: Unevaluated on 1st level is invalid is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties + single cyclic ref: Unevaluated on 2nd level is invalid is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties + single cyclic ref: Unevaluated on 3rd level is invalid is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: dynamic evalation inside nested refs: xx + foo is invalid is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties not affected by propertyNames: string property is invalid is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties can see annotations from if without then and else: invalid in case if is evaluated is expected to be invalid',
            '[draft2019-09/anchor.json]: Location-independent identifier: mismatch is expected to be invalid',
            '[draft2019-09/anchor.json]: Location-independent identifier with absolute URI: mismatch is expected to be invalid',
            '[draft2019-09/anchor.json]: Location-independent identifier with base URI change in subschema: mismatch is expected to be invalid',
            '[draft2019-09/anchor.json]: $anchor inside an enum is not a real identifier: in implementations that strip $anchor, this may match either $def is expected to be invalid',
            '[draft2019-09/anchor.json]: $anchor inside an enum is not a real identifier: no match on enum or $ref to $anchor is expected to be invalid',
            '[draft2019-09/anchor.json]: same $anchor with different base uri: $ref does not resolve to /$defs/A/allOf/0 is expected to be invalid',

        ];

        if ($this->is32Bit()) {
            $skip[] = '[draft4/multipleOf.json]: small multiple of large integer: any integer is a multiple of 1e-8 is expected to be valid'; // Test case contains a number which doesn't fit in 32 bits
        }

        return in_array($name, $skip, true);
    }

    private function is32Bit(): bool
    {
        return PHP_INT_SIZE === 4;
    }

    /**
     * @phpstan-return int-mask-of<Constraint::CHECK_MODE_*>
     */
    private function getCheckModeForDraft(string $draft): int
    {
        switch ($draft) {
            case 'draft6':
            case 'draft7':
            case 'draft2019-09':
                return Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_STRICT;
            default:
                return Constraint::CHECK_MODE_NORMAL;
        }
    }
}
