<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft2019;

use Generator;
use JsonSchema\Constraints\Drafts\Draft2019\FormatConstraint;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;

class FormatConstraintTest extends VeryBaseTestCase
{
    /**
     * @dataProvider getInvalidFormats
     */
    public function testInvalidFormat($string, $format): void
    {
        $validator = new FormatConstraint();
        $schema = new \stdClass();
        $schema->format = $format;

        $validator->check($string, $schema);
        $this->assertCount(1, $validator->getErrors(), 'Expected 1 error');
    }

    /**
     * @dataProvider getValidFormats
     */
    public function testValidFormat($string, $format): void
    {
        $validator = new FormatConstraint();
        $schema = new \stdClass();
        $schema->format = $format;

        $validator->check($string, $schema);

        $this->assertTrue($validator->isValid());
    }

    public function getInvalidFormats(): Generator
    {
        yield 'Date-time format with value containing null byte' => ["2020-01-01T12:34:56\x00", 'date-time'];
        yield 'Date format with value containing null byte' => ["2020-01-01\x00", 'date'];
        yield 'Time format with value containing null byte' => ["13:37:00\x00", 'time'];
        yield 'UUID format with trailing new line' => ["2eb8aa08-aa98-11ea-b4aa-73b441d16380\n", 'uuid'];
        yield 'URI template format with trailing new line' => ["http://example.com/{term}\n", 'uri-template'];
        yield 'IRI format with trailing new line' => ["http://例え.jp/π\n", 'iri'];
        yield 'IRI format with relative reference' => ['/π', 'iri'];
        yield 'IRI reference format with lone percent sign' => ['/π%', 'iri-reference'];
        yield 'IRI format with IPvFuture literal without address' => ['http://[v1.]/', 'iri'];
    }

    public function getValidFormats(): Generator
    {
        yield 'Date-time format with value containing high-precision fractional seconds' => ['2020-01-01T12:00:02.0000001Z', 'date-time'];
        yield 'UUID format' => ['2eb8aa08-aa98-11ea-b4aa-73b441d16380', 'uuid'];
        yield 'URI template format with non-Latin literal' => ['http://例え.jp/π/{term}', 'uri-template'];
        yield 'IRI format with non-Latin characters' => ['http://例え.jp/π?q=日本#frag', 'iri'];
        yield 'IRI reference format with long path' => ['/' . str_repeat('π', 100000), 'iri-reference'];
        yield 'IRI format with IPvFuture literal' => ['http://[v1.fe:a]/π', 'iri'];
        yield 'IRI reference format with IPvFuture literal' => ['//[V1.fe]/p', 'iri-reference'];
    }
}
