<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;
use JsonSchema\UriRetrieverInterface;

/**
 * @package JsonSchema\Tests\Constraints
 */
abstract class VeryBaseTestCase extends TestCase
{
    private const DRAFT_SCHEMA_DIR = __DIR__ . '/../../dist/schema/';
    private const TEST_SUITE_REMOTES =  __DIR__ . '/../../vendor/json-schema/json-schema-test-suite/remotes';

    /** @var array<string, stdClass>  */
    private $draftSchemas = [];

    protected function getUriRetrieverMock(?object $schema): object
    {
        $uriRetriever = $this->prophesize(UriRetrieverInterface::class);
        $uriRetriever->retrieve('http://www.my-domain.com/schema.json')
            ->willReturn($schema)
            ->shouldBeCalled();

        $uriRetriever->retrieve(Argument::any())
            ->will(function ($args): stdClass  {
                if ('http://json-schema.org/draft-03/schema' === $args[0]) {
                    return $this->getDraftSchema('json-schema-draft-03.json');
                }

                if ('http://json-schema.org/draft-04/schema' === $args[0]) {
                    return $this->getDraftSchema('json-schema-draft-04.json');
                }

                $urlParts = parse_url($args[0]);

                if (0 === strpos($args[0], 'http://localhost:1234')) {
                    return $this->readAndJsonDecodeFile(self::TEST_SUITE_REMOTES . $urlParts['path']);
                }

                if (0 === strpos($args[0], 'http://www.my-domain.com')) {
                    return $this->readAndJsonDecodeFile(self::TEST_SUITE_REMOTES . '/folder' . $urlParts['path']);
                }

                throw new \InvalidArgumentException(sprintf('No handling for %s has been setup', $args[0]));
            });

        return $uriRetriever->reveal();
    }

    private function getDraftSchema(string $draft): stdClass
    {
        if (!array_key_exists($draft, $this->draftSchemas)) {
            $this->draftSchemas[$draft] = $this->readAndJsonDecodeFile(self::DRAFT_SCHEMA_DIR . '/' . $draft);
        }

        return $this->draftSchemas[$draft];
    }

    private function readAndJsonDecodeFile(string $file): stdClass
    {
        return json_decode(file_get_contents($file), false);
    }
}
