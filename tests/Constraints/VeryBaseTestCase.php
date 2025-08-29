<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\DraftIdentifiers;
use JsonSchema\UriRetrieverInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

abstract class VeryBaseTestCase extends TestCase
{
    private const DRAFT_SCHEMA_DIR = __DIR__ . '/../../dist/schema/';
    private const TEST_SUITE_REMOTES =  __DIR__ . '/../../vendor/json-schema/json-schema-test-suite/remotes';

    /** @var array<string, stdClass> */
    private $draftSchemas = [];

    /**
     * @param object|bool|null $schema
     *
     * @return object
     */
    protected function getUriRetrieverMock($schema): object
    {
        $uriRetriever = $this->prophesize(UriRetrieverInterface::class);
        $uriRetriever->retrieve($schema->id ?? 'http://www.my-domain.com/schema.json')
            ->willReturn($schema)
            ->shouldBeCalled();

        $that = $this;
        $uriRetriever->retrieve(Argument::any())
            ->will(function ($args) use ($that): stdClass {
                if (strpos($args[0], DraftIdentifiers::DRAFT_3()->withoutFragment()) === 0) {
                    return $that->getDraftSchema('json-schema-draft-03.json');
                }

                if (strpos($args[0], DraftIdentifiers::DRAFT_4()->withoutFragment()) === 0) {
                    return $that->getDraftSchema('json-schema-draft-04.json');
                }
                if (strpos($args[0],DraftIdentifiers::DRAFT_6()->withoutFragment()) === 0) {
                    return $that->getDraftSchema('json-schema-draft-06.json');
                }

                $urlParts = parse_url($args[0]);

                if (0 === strpos($args[0], 'http://localhost:1234')) {
                    return $that->readAndJsonDecodeFile(self::TEST_SUITE_REMOTES . $urlParts['path']);
                }

                if (0 === strpos($args[0], 'http://www.my-domain.com')) {
                    return $that->readAndJsonDecodeFile(self::TEST_SUITE_REMOTES . '/folder' . $urlParts['path']);
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
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist', $file));
        }

        return json_decode(file_get_contents($file), false);
    }

    protected function is32Bit(): bool
    {
        return PHP_INT_SIZE === 4;
    }
}
