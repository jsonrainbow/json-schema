<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

/**
 * @package JsonSchema\Tests\Constraints
 */
abstract class VeryBaseTestCase extends TestCase
{
    private const DRAFT_SCHEMA_DIR = __DIR__ . '/../../dist/schema/';
    /** @var array<string, stdClass>  */
    private $draftSchemas = [];

    protected function getUriRetrieverMock(?object $schema): object
    {
        $relativeTestsRoot = realpath(__DIR__ . '/../../vendor/json-schema/json-schema-test-suite/remotes');


        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve('http://www.my-domain.com/schema.json')
            ->willReturn($schema)
            ->shouldBeCalled();

        $uriRetriever->retrieve(Argument::any())
            ->will(function ($args) use ($relativeTestsRoot) {
                if ('http://json-schema.org/draft-03/schema' === $args[0]) {
                    return $this->getDraftSchema('json-schema-draft-03.json');
                }

                if ('http://json-schema.org/draft-04/schema' === $args[0]) {
                    return $this->getDraftSchema('json-schema-draft-04.json');
                }

                if (0 === strpos($args[0], 'http://localhost:1234')) {
                    $urlParts = parse_url($args[0]);

                    return json_decode(file_get_contents($relativeTestsRoot . $urlParts['path']));
                } elseif (0 === strpos($args[0], 'http://www.my-domain.com')) {
                    $urlParts = parse_url($args[0]);

                    return json_decode(file_get_contents($relativeTestsRoot . '/folder' . $urlParts['path']));
                }
            });

        return $uriRetriever->reveal();
    }

    private function getDraftSchema(string $draft): stdClass
    {
        if (!array_key_exists($draft, $this->draftSchemas)) {
            $this->draftSchemas[$draft] = json_decode(file_get_contents(self::DRAFT_SCHEMA_DIR . '/' . $draft), false);
        }

        return $this->draftSchemas[$draft];
    }
}
