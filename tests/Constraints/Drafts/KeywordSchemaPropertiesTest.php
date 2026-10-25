<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Drafts;

use JsonSchema\Constraints\Drafts\Draft06;
use JsonSchema\Constraints\Drafts\Draft07;
use JsonSchema\Constraints\Drafts\Draft2019;
use PHPUnit\Framework\TestCase;

/**
 * The draft constraints only dispatch a keyword when one of the schema properties it maps to is
 * present, so a keyword guarding on a property missing from that map would never be checked.
 */
class KeywordSchemaPropertiesTest extends TestCase
{
    /**
     * @dataProvider keywordProvider
     *
     * @param class-string $draftConstraintClass
     * @param class-string $keywordConstraintClass
     */
    public function testDispatcherChecksThePropertiesTheConstraintGuardsOn(
        string $draftConstraintClass,
        string $keyword,
        string $keywordConstraintClass
    ): void {
        $guardedProperties = $this->getGuardedSchemaProperties($keywordConstraintClass);
        if ($guardedProperties === []) {
            $this->markTestSkipped(sprintf('%s does not guard on any schema property', $keywordConstraintClass));
        }

        $keywordSchemaProperties = (new \ReflectionClassConstant($draftConstraintClass, 'KEYWORD_SCHEMA_PROPERTIES'))->getValue();
        $dispatchedProperties = $keywordSchemaProperties[$keyword] ?? [$keyword];

        sort($guardedProperties);
        sort($dispatchedProperties);
        self::assertSame(
            $guardedProperties,
            $dispatchedProperties,
            sprintf('Add the "%s" keyword to %s::KEYWORD_SCHEMA_PROPERTIES', $keyword, $draftConstraintClass)
        );
    }

    public function keywordProvider(): \Generator
    {
        $drafts = [
            Draft06\Draft06Constraint::class => Draft06\Factory::class,
            Draft07\Draft07Constraint::class => Draft07\Factory::class,
            Draft2019\Draft2019Constraint::class => Draft2019\Factory::class,
        ];

        foreach ($drafts as $draftConstraintClass => $factoryClass) {
            $constraintMap = new \ReflectionProperty($factoryClass, 'constraintMap');
            if (PHP_VERSION_ID < 80100) {
                $constraintMap->setAccessible(true);
            }

            foreach ($constraintMap->getValue(new $factoryClass()) as $keyword => $keywordConstraintClass) {
                if ($keyword === 'schema') {
                    continue;
                }

                yield sprintf('%s: %s', (new \ReflectionClass($draftConstraintClass))->getShortName(), $keyword) => [
                    $draftConstraintClass,
                    $keyword,
                    $keywordConstraintClass,
                ];
            }
        }
    }

    /**
     * The schema properties named in the first condition of the constraint's check(), which is
     * where every constraint returns early when its keyword is absent.
     *
     * @param class-string $constraintClass
     *
     * @return list<string>
     */
    private function getGuardedSchemaProperties(string $constraintClass): array
    {
        $check = new \ReflectionMethod($constraintClass, 'check');
        $lines = file((string) $check->getFileName());
        if ($lines === false) {
            self::fail(sprintf('Unable to read the source of %s', $constraintClass));
        }
        $startLine = (int) $check->getStartLine();
        $body = implode('', array_slice($lines, $startLine, (int) $check->getEndLine() - $startLine));

        if (!preg_match('/^\s*if \((.*)\) \{$/m', $body, $condition)) {
            return [];
        }

        preg_match_all('/property_exists\(\$schema, \'([^\']+)\'\)/', $condition[1], $properties);

        return $properties[1];
    }
}
