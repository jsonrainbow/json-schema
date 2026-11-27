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
        bool $expectedValidationResult
    ): void {
        $schemaStorage = new SchemaStorage();
        $id = is_object($schema) && property_exists($schema, 'id') ? $schema->id : SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        $schemaStorage->addSchema($id, $schema);
        $this->loadRemotesIntoStorage($schemaStorage);
        $factory = new Factory($schemaStorage);
        $factory->setDefaultDialect($draft->getValue());
        $validator = new Validator($factory);

        $validator->validate($data, $schema, $checkMode);

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
        $skippedDrafts = ['draft-next', 'latest', 'v1'];

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
                        $filename = str_replace('\\', '/', preg_replace('#^.*[/\\\\]tests[/\\\\]#', '', $file->getRealPath()));
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
                            'checkMode' => $this->getCheckModeForTest($baseDraftName, $filename),
                            'draft' => DraftIdentifiers::fromConstraintName($baseDraftName),
                            'expectedValidationResult' => $test->valid,
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

            $relativePath = str_replace('\\', '/', substr($info->getPathname(), strlen($remotesDir)));
            $id = 'http://localhost:1234' . $relativePath;
            $storage->addSchema($id, json_decode(file_get_contents($info->getPathname()), false));
        }
    }

    private function shouldNotYieldTest(string $name): bool
    {
        $skip = [
            // Draft 2019-09 complex constraints, which aren't supported initially
            '[draft2019-09/recursiveRef.json]: $recursiveRef without $recursiveAnchor works like $ref: recursive mismatch is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef without using nesting: integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef without using nesting: two levels, no match is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with $recursiveAnchor: false works like $ref: integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with $recursiveAnchor: false works like $ref: two levels, integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor works like $ref: integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor works like $ref: two levels, integer does not match as a property value is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor in the outer schema resource: leaf node does not match: recursion only uses inner schema is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: $recursiveRef with no $recursiveAnchor in the initial target schema resource: leaf node does not match: recursion uses the inner schema is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: multiple dynamic paths to the $recursiveRef keyword: recurse to integerNode - floats are not allowed is expected to be invalid',
            '[draft2019-09/recursiveRef.json]: dynamic $recursiveRef destination (not predictable at schema compile time): integer node is expected to be invalid',
            '[draft2019-09/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: applicator vocabulary still works is expected to be invalid',
            '[draft2019-09/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: no validation: valid number is expected to be valid',
            '[draft2019-09/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: no validation: invalid number, but it still validates is expected to be valid',
            '[draft2019-09/vocabulary.json]: ignore unrecognized optional vocabulary: string value is expected to be invalid',
            '[draft2019-09/vocabulary.json]: ignore unrecognized optional vocabulary: number value is expected to be valid',
            '[draft2019-09/ref.json]: $ref with $recursiveAnchor: extra items disallowed for root is expected to be invalid',
            '[draft2019-09/unevaluatedProperties.json]: unevaluatedProperties with $recursiveRef: with no unevaluated properties is expected to be valid',
            '[draft2019-09/unevaluatedItems.json]: unevaluatedItems with $recursiveRef: with no unevaluated items is expected to be valid',
            // Draft 2020-12: $vocabulary is not supported
            '[draft2020-12/optional/format-assertion.json]: schema that uses custom metaschema with format-assertion: false: format-assertion: false: invalid string is expected to be invalid',
            '[draft2020-12/optional/format-assertion.json]: schema that uses custom metaschema with format-assertion: false: format-assertion: false: valid string is expected to be valid',
            '[draft2020-12/optional/format-assertion.json]: schema that uses custom metaschema with format-assertion: true: format-assertion: true: invalid string is expected to be invalid',
            '[draft2020-12/optional/format-assertion.json]: schema that uses custom metaschema with format-assertion: true: format-assertion: true: valid string is expected to be valid',
            '[draft2020-12/vocabulary.json]: ignore unrecognized optional vocabulary: number value is expected to be valid',
            '[draft2020-12/vocabulary.json]: ignore unrecognized optional vocabulary: string value is expected to be invalid',
            '[draft2020-12/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: applicator vocabulary still works is expected to be invalid',
            '[draft2020-12/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: no validation: invalid number, but it still validates is expected to be valid',
            '[draft2020-12/vocabulary.json]: schema that uses custom metaschema with with no validation vocabulary: no validation: valid number is expected to be valid',
            // Draft 2020-12: $dynamicRef and $dynamicAnchor (successors of $recursiveRef and $recursiveAnchor) are not supported
            '[draft2020-12/dynamicRef.json]: $dynamicRef avoids the root of each schema, but scopes are still registered: data is not sufficient for schema at second#/$defs/length is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: $dynamicRef points to a boolean schema: follow $dynamicRef to a false schema is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: $dynamicRef skips over intermediate resources - direct reference: string property fails is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: $ref and $dynamicAnchor are independent of order - $defs first: incorrect extended schema is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: $ref and $dynamicAnchor are independent of order - $ref first: incorrect extended schema is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: $ref to $dynamicRef finds detached $dynamicAnchor: non-number is invalid is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: A $dynamicRef resolves to the first $dynamicAnchor still in scope that is encountered when the schema is evaluated: An array containing non-strings is invalid is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: A $dynamicRef that initially resolves to a schema with a matching $dynamicAnchor resolves to the first $dynamicAnchor in the dynamic scope: The recursive part is not valid against the root is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: A $dynamicRef to a $dynamicAnchor in the same schema resource behaves like a normal $ref to an $anchor: An array containing non-strings is invalid is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: A $dynamicRef to an $anchor in the same schema resource behaves like a normal $ref to an $anchor: An array containing non-strings is invalid is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: A $dynamicRef with intermediate scopes that don\'t include a matching $dynamicAnchor does not affect dynamic scope resolution: An array containing non-strings is invalid is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: A $dynamicRef without anchor in fragment behaves identical to $ref: An array of strings is invalid is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: A $ref to a $dynamicAnchor in the same schema resource behaves like a normal $ref to an $anchor: An array containing non-strings is invalid is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: A $ref to a $dynamicAnchor in the same schema resource behaves like a normal $ref to an $anchor: An array of strings is valid is expected to be valid',
            '[draft2020-12/dynamicRef.json]: after leaving a dynamic scope, it is not used by a $dynamicRef: first_scope is not in dynamic scope for the $dynamicRef is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: after leaving a dynamic scope, it is not used by a $dynamicRef: string matches /$defs/thingy, but the $dynamicRef does not stop here is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: multiple dynamic paths to the $dynamicRef keyword: number list with string values is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: multiple dynamic paths to the $dynamicRef keyword: string list with number values is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: strict-tree schema, guards against misspelled properties: instance with misspelled field is expected to be invalid',
            '[draft2020-12/dynamicRef.json]: tests for implementation dynamic anchor and reference link: incorrect extended schema is expected to be invalid',
            '[draft2020-12/optional/dynamicRef.json]: $dynamicRef skips over intermediate resources - pointer reference across resource boundary: string property fails is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with $dynamicRef: with unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedProperties.json]: unevaluatedProperties with $dynamicRef: with no unevaluated properties is expected to be valid',
            // Draft 2020-12: unevaluatedItems is not supported
            '[draft2020-12/unevaluatedItems.json]: Evaluated items collection needs to consider instance location: with an unevaluated item that exists at another location is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: item is evaluated in an uncle schema to unevaluatedItems: uncle keyword evaluation is not significant is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems and contains interact to control item dependency relationship: only a\'s and c\'s are invalid is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems and contains interact to control item dependency relationship: only b\'s and c\'s are invalid is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems and contains interact to control item dependency relationship: only b\'s are invalid is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems and contains interact to control item dependency relationship: only c\'s are invalid is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems as schema: with invalid unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems before $ref: with unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems can see annotations from if without then and else: invalid in case if is evaluated is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems can\'t see inside cousins: always fails is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems depends on adjacent contains: contains passes, second item is not evaluated is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems depends on multiple nested contains: 7 not evaluated, fails unevaluatedItems is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems false: with unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with $ref: with unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with anyOf: when one schema matches and has unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with anyOf: when two schemas match and has unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with boolean schemas: with unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with if/then/else: when if doesn\'t match and it has unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with if/then/else: when if matches and it has unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with minContains = 0: no items evaluated by contains is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with minContains = 0: some but not all items evaluated by contains is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with nested items: with invalid additional item is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with nested tuple: with unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with not: with unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with oneOf: with unevaluated items is expected to be invalid',
            '[draft2020-12/unevaluatedItems.json]: unevaluatedItems with tuple: with unevaluated items is expected to be invalid',
            // Draft 2020-12 optional: the same limitations as in Draft 2019-09
            '[draft2020-12/optional/cross-draft.json]: refs to historic drafts are processed as historic drafts: first item not a string is valid is expected to be valid',
            '[draft2020-12/optional/float-overflow.json]: all integers are multiples of 0.5, if overflow is handled: valid if optional overflow handling is implemented is expected to be valid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: ECMA 262 character classes and escapes: a negated empty character class is valid ECMA 262 is expected to be valid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: ECMA 262 character classes and escapes: an empty character class is valid ECMA 262 is expected to be valid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: ECMA 262 lookbehind is valid, including variable width: a variable-width lookbehind (ES2018) is expected to be valid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: Python named backreference (?P=name) is not ECMA 262 is expected to be invalid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: Python named group (?P<name>...) is not ECMA 262 is expected to be invalid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: inline comment group (?#...) is not ECMA 262 is expected to be invalid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: \a is not an ECMA 262 control escape: when used as a pattern is expected to be invalid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: global inline flag groups are not valid ECMA 262: a single global inline flag (?i) is expected to be invalid',
            '[draft2020-12/optional/format/ecmascript-regex.json]: global inline flag groups are not valid ECMA 262: multiple global inline flags (?ims) is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Arabic-Indic digits mixed with Extended Arabic-Indic digits is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with a Nonspacing Mark is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with a Spacing Combining Mark is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with an Enclosing Mark is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Exceptions that are DISALLOWED, left-to-right chars is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Exceptions that are DISALLOWED, right-to-left chars is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Greek KERAIA not followed by Greek is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Greek KERAIA not followed by anything is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERESH not preceded by Hebrew is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERESH not preceded by anything is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERSHAYIM not preceded by Hebrew is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERSHAYIM not preceded by anything is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: KATAKANA MIDDLE DOT with no Hiragana, Katakana, or Han is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: KATAKANA MIDDLE DOT with no other characters is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with no following \'l\' is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with no preceding \'l\' is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with nothing following is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with nothing preceding is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: ZERO WIDTH JOINER not preceded by Virama is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: ZERO WIDTH JOINER not preceded by anything is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: contains "--" in the 3rd and 4th position is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: contains illegal char U+302E Hangul single dot tone mark is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of A-label (punycode) host names: invalid Punycode is expected to be invalid',
            '[draft2020-12/optional/format/hostname.json]: validation of host names: exceeds maximum overall length (256) is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: A-label that decodes to a Bidi rule violation is invalid is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: A-label that decodes to a disallowed code point is invalid is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: Bidi domain name with a digit-first label is invalid is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: Exceptions that are DISALLOWED, left-to-right chars is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: Exceptions that are DISALLOWED, right-to-left chars is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: KATAKANA MIDDLE DOT with no Hiragana, Katakana, or Han is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: KATAKANA MIDDLE DOT with no other characters is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with no following \'l\' is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with no preceding \'l\' is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with nothing following is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with nothing preceding is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH JOINER preceded by Virama is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH NON-JOINER not preceded by Virama but matches regexp is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH NON-JOINER preceded by Virama is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: a label of only Arabic-Indic digits is invalid is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: contains illegal char U+302E Hangul single dot tone mark is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: label starting with a digit before a right-to-left letter is invalid is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: left-to-right label containing a right-to-left letter is invalid is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of internationalized host names: right-to-left label mixing both digit types is invalid is expected to be invalid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of separators in internationalized host names: fullwidth full stop as label separator is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of separators in internationalized host names: halfwidth ideographic full stop as label separator is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of separators in internationalized host names: ideographic full stop as label separator is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (fullwidth full stop) is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (halfwidth ideographic full stop) is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (ideographic full stop) is expected to be valid',
            '[draft2020-12/optional/format/idn-hostname.json]: validation of separators in internationalized host names: trailing dot is expected to be invalid',
            '[draft2020-12/optional/format/time.json]: validation of time strings: a valid time string with a 15 digit second fraction is expected to be valid',
            '[draft2020-12/optional/format/time.json]: validation of time strings: time with unknown local offset is valid is expected to be valid',
            '[draft2020-12/optional/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf is expected to be invalid',
            '[draft2020-12/optional/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it is expected to be valid',
            // Optional: bignum — PHP does not natively support arbitrary-precision integers/floats
            '[draft3/optional/bignum.json]: integer: a bignum is an integer is expected to be valid',
            '[draft3/optional/bignum.json]: integer: a negative bignum is an integer is expected to be valid',
            '[draft4/optional/bignum.json]: integer: a bignum is an integer is expected to be valid',
            '[draft4/optional/bignum.json]: integer: a negative bignum is an integer is expected to be valid',
            '[draft3/optional/bignum.json]: divisibleBy with integers above 2^53: 1e20 is not divisible by 3 is expected to be invalid',
            '[draft4/optional/bignum.json]: multipleOf with integers above 2^53: 1e20 is not multiple of 3 is expected to be invalid',
            '[draft6/optional/bignum.json]: multipleOf with integers above 2^53: 1e20 is not multiple of 3 is expected to be invalid',
            '[draft7/optional/bignum.json]: multipleOf with integers above 2^53: 1e20 is not multiple of 3 is expected to be invalid',
            '[draft2019-09/optional/bignum.json]: multipleOf with integers above 2^53: 1e20 is not multiple of 3 is expected to be invalid',

            // Optional: float-overflow — PHP float precision differs from the ECMAScript model
            '[draft4/optional/float-overflow.json]: all integers are multiples of 0.5, if overflow is handled: valid if optional overflow handling is implemented is expected to be valid',
            '[draft6/optional/float-overflow.json]: all integers are multiples of 0.5, if overflow is handled: valid if optional overflow handling is implemented is expected to be valid',
            '[draft7/optional/float-overflow.json]: all integers are multiples of 0.5, if overflow is handled: valid if optional overflow handling is implemented is expected to be valid',
            '[draft2019-09/optional/float-overflow.json]: all integers are multiples of 0.5, if overflow is handled: valid if optional overflow handling is implemented is expected to be valid',
            // Optional: cross-draft — cross-draft schema resolution ($ref across drafts) is not implemented
            '[draft7/optional/cross-draft.json]: refs to future drafts are processed as future drafts: missing bar is invalid is expected to be invalid',
            '[draft2019-09/optional/cross-draft.json]: refs to future drafts are processed as future drafts: first item not a string is invalid is expected to be invalid',
            '[draft2019-09/optional/cross-draft.json]: refs to historic drafts are processed as historic drafts: missing bar is valid is expected to be valid',
            // Optional: format ecmascript regex validation is not (yet) implemented
            '[draft3/optional/format/ecmascript-regex.json]: ECMA 262 regex dialect recognition: [^] is a valid regex is expected to be valid',
            '[draft3/optional/format/ecmascript-regex.json]: ECMA 262 regex dialect recognition: ECMA 262 does not support Python-style named groups is expected to be invalid',
            '[draft7/optional/format/ecmascript-regex.json]: \a is not an ECMA 262 control escape: when used as a pattern is expected to be invalid',
            '[draft7/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: Python named group (?P<name>...) is not ECMA 262 is expected to be invalid',
            '[draft7/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: Python named backreference (?P=name) is not ECMA 262 is expected to be invalid',
            '[draft7/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: inline comment group (?#...) is not ECMA 262 is expected to be invalid',
            '[draft7/optional/format/ecmascript-regex.json]: global inline flag groups are not valid ECMA 262: a single global inline flag (?i) is expected to be invalid',
            '[draft7/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: Python named backreference (?P=name) is not ECMA 262 is expected to be invalid',
            '[draft7/optional/format/ecmascript-regex.json]: ECMA 262 lookbehind is valid, including variable width: a variable-width lookbehind (ES2018) is expected to be valid',
            '[draft7/optional/format/ecmascript-regex.json]: ECMA 262 character classes and escapes: an empty character class is valid ECMA 262 is expected to be valid',
            '[draft7/optional/format/ecmascript-regex.json]: ECMA 262 character classes and escapes: a negated empty character class is valid ECMA 262 is expected to be valid',
            '[draft7/optional/format/ecmascript-regex.json]: global inline flag groups are not valid ECMA 262: multiple global inline flags (?ims) is expected to be invalid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: ECMA 262 lookbehind is valid, including variable width: a variable-width lookbehind (ES2018) is expected to be valid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: ECMA 262 character classes and escapes: an empty character class is valid ECMA 262 is expected to be valid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: ECMA 262 character classes and escapes: a negated empty character class is valid ECMA 262 is expected to be valid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: \a is not an ECMA 262 control escape: when used as a pattern is expected to be invalid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: Python named group (?P<name>...) is not ECMA 262 is expected to be invalid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: Python named backreference (?P=name) is not ECMA 262 is expected to be invalid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: Python-specific regular expression syntax is not valid ECMA 262: inline comment group (?#...) is not ECMA 262 is expected to be invalid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: global inline flag groups are not valid ECMA 262: a single global inline flag (?i) is expected to be invalid',
            '[draft2019-09/optional/format/ecmascript-regex.json]: global inline flag groups are not valid ECMA 262: multiple global inline flags (?ims) is expected to be invalid',
            // Optional: idn-hostname — IDN hostname format validation is not implemented
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: Exceptions that are DISALLOWED, left-to-right chars is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: Exceptions that are DISALLOWED, right-to-left chars is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: KATAKANA MIDDLE DOT with no Hiragana, Katakana, or Han is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: KATAKANA MIDDLE DOT with no other characters is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with no following \'l\' is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with no preceding \'l\' is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with nothing following is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with nothing preceding is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH JOINER preceded by Virama is expected to be valid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH NON-JOINER not preceded by Virama but matches regexp is expected to be valid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH NON-JOINER preceded by Virama is expected to be valid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: contains illegal char U+302E Hangul single dot tone mark is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: a label of only Arabic-Indic digits is invalid is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: Bidi domain name with a digit-first label is invalid is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: label starting with a digit before a right-to-left letter is invalid is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: left-to-right label containing a right-to-left letter is invalid is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: right-to-left label mixing both digit types is invalid is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: A-label that decodes to a disallowed code point is invalid is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of internationalized host names: A-label that decodes to a Bidi rule violation is invalid is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of separators in internationalized host names: ideographic full stop as label separator is expected to be valid',
            '[draft7/optional/format/idn-hostname.json]: validation of separators in internationalized host names: fullwidth full stop as label separator is expected to be valid',
            '[draft7/optional/format/idn-hostname.json]: validation of separators in internationalized host names: halfwidth ideographic full stop as label separator is expected to be valid',
            '[draft7/optional/format/idn-hostname.json]: validation of separators in internationalized host names: trailing dot is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (ideographic full stop) is expected to be valid',
            '[draft7/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (fullwidth full stop) is expected to be valid',
            '[draft4/optional/format/hostname.json]: validation of host names: exceeds maximum overall length (256) is expected to be invalid',
            '[draft6/optional/format/hostname.json]: validation of host names: exceeds maximum overall length (256) is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of host names: exceeds maximum overall length (256) is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: invalid Punycode is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: contains illegal char U+302E Hangul single dot tone mark is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with a Spacing Combining Mark is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with a Nonspacing Mark is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with an Enclosing Mark is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Exceptions that are DISALLOWED, right-to-left chars is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Exceptions that are DISALLOWED, left-to-right chars is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with no preceding \'l\' is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with nothing preceding is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with no following \'l\' is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with nothing following is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Greek KERAIA not followed by Greek is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Greek KERAIA not followed by anything is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERESH not preceded by Hebrew is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERESH not preceded by anything is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERSHAYIM not preceded by Hebrew is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERSHAYIM not preceded by anything is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: KATAKANA MIDDLE DOT with no Hiragana, Katakana, or Han is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: KATAKANA MIDDLE DOT with no other characters is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: Arabic-Indic digits mixed with Extended Arabic-Indic digits is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: ZERO WIDTH JOINER not preceded by Virama is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: ZERO WIDTH JOINER not preceded by anything is expected to be invalid',
            '[draft7/optional/format/hostname.json]: validation of A-label (punycode) host names: contains "--" in the 3rd and 4th position is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: invalid Punycode is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: contains illegal char U+302E Hangul single dot tone mark is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with a Spacing Combining Mark is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with a Nonspacing Mark is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Begins with an Enclosing Mark is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Exceptions that are DISALLOWED, right-to-left chars is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Exceptions that are DISALLOWED, left-to-right chars is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with no preceding \'l\' is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with nothing preceding is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with no following \'l\' is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with nothing following is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: MIDDLE DOT with nothing following is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Greek KERAIA not followed by anything is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERESH not preceded by Hebrew is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERESH not preceded by anything is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERSHAYIM not preceded by Hebrew is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Hebrew GERSHAYIM not preceded by anything is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: KATAKANA MIDDLE DOT with no Hiragana, Katakana, or Han is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: KATAKANA MIDDLE DOT with no other characters is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Arabic-Indic digits mixed with Extended Arabic-Indic digits is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: ZERO WIDTH JOINER not preceded by Virama is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: ZERO WIDTH JOINER not preceded by anything is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: contains "--" in the 3rd and 4th position is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of host names: exceeds maximum overall length (256) is expected to be invalid',
            '[draft2019-09/optional/format/hostname.json]: validation of A-label (punycode) host names: Greek KERAIA not followed by Greek is expected to be invalid',
            '[draft7/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (halfwidth ideographic full stop) is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: contains illegal char U+302E Hangul single dot tone mark is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: Exceptions that are DISALLOWED, right-to-left chars is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: Exceptions that are DISALLOWED, left-to-right chars is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with no preceding \'l\' is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with nothing preceding is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with no following \'l\' is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: MIDDLE DOT with nothing following is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: KATAKANA MIDDLE DOT with no Hiragana, Katakana, or Han is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: KATAKANA MIDDLE DOT with no other characters is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH JOINER preceded by Virama is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH NON-JOINER preceded by Virama is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: ZERO WIDTH NON-JOINER not preceded by Virama but matches regexp is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: a label of only Arabic-Indic digits is invalid is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: Bidi domain name with a digit-first label is invalid is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: label starting with a digit before a right-to-left letter is invalid is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: left-to-right label containing a right-to-left letter is invalid is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: right-to-left label mixing both digit types is invalid is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: A-label that decodes to a disallowed code point is invalid is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of internationalized host names: A-label that decodes to a Bidi rule violation is invalid is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of separators in internationalized host names: ideographic full stop as label separator is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of separators in internationalized host names: fullwidth full stop as label separator is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of separators in internationalized host names: halfwidth ideographic full stop as label separator is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of separators in internationalized host names: trailing dot is expected to be invalid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (ideographic full stop) is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (fullwidth full stop) is expected to be valid',
            '[draft2019-09/optional/format/idn-hostname.json]: validation of separators in internationalized host names: label too long if separator ignored (halfwidth ideographic full stop) is expected to be valid',
            // Optional format time:
            '[draft7/optional/format/time.json]: validation of time strings: time with unknown local offset is valid is expected to be valid',
            '[draft7/optional/format/time.json]: validation of time strings: a valid time string with a 15 digit second fraction is expected to be valid',
            '[draft2019-09/optional/format/time.json]: validation of time strings: time with unknown local offset is valid is expected to be valid', // Input contains -00:00 fails on last comparison.
            '[draft2019-09/optional/format/time.json]: validation of time strings: a valid time string with a 15 digit second fraction is expected to be valid', // should be handled same as PR#940
            // Optional unknown keyword: Not yet implemented
            '[draft6/optional/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it is expected to be valid',
            '[draft6/optional/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf is expected to be invalid',
            '[draft7/optional/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it is expected to be valid',
            '[draft7/optional/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf is expected to be invalid',
            '[draft2019-09/optional/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it is expected to be valid',
            '[draft2019-09/optional/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf is expected to be invalid',
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
    private function getCheckModeForTest(string $draft, string $filename): int
    {
        $checkMode = $this->getCheckModeForDraft($draft);
        // Format is an annotation by default as of Draft 2020-12, the optional format tests expect it to be asserted
        if ($draft === 'draft2020-12' && strpos($filename, 'draft2020-12/optional/format/') === 0) {
            $checkMode |= Constraint::CHECK_MODE_FORMAT_ASSERTION;
        }

        return $checkMode;
    }

    private function getCheckModeForDraft(string $draft): int
    {
        switch ($draft) {
            case 'draft6':
            case 'draft7':
            case 'draft2019-09':
            case 'draft2020-12':
                return Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_STRICT;
            default:
                return Constraint::CHECK_MODE_NORMAL;
        }
    }
}
