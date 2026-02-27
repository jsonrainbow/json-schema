<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @package JsonSchema\Tests\Constraints
 */
abstract class VeryBaseTestCase extends TestCase
{
    /** @var object */
    private $jsonSchemaDraft03;

    /** @var object */
    private $jsonSchemaDraft04;

    /**
     * @param object $schema
     *
     * @return object
     */
    protected function getUriRetrieverMock($schema)
    {
        $relativeTestsRoot = realpath(__DIR__ . '/../../vendor/json-schema/JSON-Schema-Test-Suite/remotes');

        $jsonSchemaDraft03 = $this->getJsonSchemaDraft03();
        $jsonSchemaDraft04 = $this->getJsonSchemaDraft04();

        $uriRetriever = $this->prophesize('JsonSchema\UriRetrieverInterface');
        $uriRetriever->retrieve('http://www.my-domain.com/schema.json')
            ->willReturn($schema)
            ->shouldBeCalled();

        $uriRetriever->retrieve(Argument::any())
            ->will(function ($args) use ($jsonSchemaDraft03, $jsonSchemaDraft04, $relativeTestsRoot) {
                if ('http://json-schema.org/draft-03/schema' === $args[0]) {
                    return $jsonSchemaDraft03;
                } elseif ('http://json-schema.org/draft-04/schema' === $args[0]) {
                    return $jsonSchemaDraft04;
                } elseif (0 === strpos($args[0], 'http://localhost:1234')) {
                    $urlParts = parse_url($args[0]);

                    return json_decode(file_get_contents($relativeTestsRoot . $urlParts['path']));
                } elseif (0 === strpos($args[0], 'http://www.my-domain.com')) {
                    $urlParts = parse_url($args[0]);

                    return json_decode(file_get_contents($relativeTestsRoot . '/folder' . $urlParts['path']));
                }
            });

        return $uriRetriever->reveal();
    }

    /**
     * @return object
     */
    private function getJsonSchemaDraft03()
    {
        if (!$this->jsonSchemaDraft03) {
            $this->jsonSchemaDraft03 = json_decode(
                file_get_contents(__DIR__ . '/../../dist/schema/json-schema-draft-03.json')
            );
        }

        return $this->jsonSchemaDraft03;
    }

    /**
     * @return object
     */
    private function getJsonSchemaDraft04()
    {
        if (!$this->jsonSchemaDraft04) {
            $this->jsonSchemaDraft04 = json_decode(
                file_get_contents(__DIR__ . '/../../dist/schema/json-schema-draft-04.json')
            );
        }

        return $this->jsonSchemaDraft04;
    }
}
