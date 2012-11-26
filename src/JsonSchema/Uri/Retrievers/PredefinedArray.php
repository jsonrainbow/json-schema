<?php

namespace JsonSchema\Uri\Retrievers;

use JsonSchema\Validator;
use JsonSchema\Uri\Retrievers\UriRetrieverInterface;
use JsonSchema\Exception\ResourceNotFoundException;

/**
 * URI retrieved based on a predefined array of schemas
 *
 * @example
 *
 *      $retriever = new PredefinedArray(array(
 *          'http://acme.com/schemas/person#'  => '{ ... }',
 *          'http://acme.com/schemas/address#' => '{ ... }',
 *      ))
 *
 *      $schema = $retriever->retrieve('http://acme.com/schemas/person#');
 */
class PredefinedArray implements UriRetrieverInterface
{
    private $schemas;
    private $contentType;

    /**
     * Constructor
     *
     * @param  array  $schemas
     * @param  string $contentType
     */
    public function __construct(array $schemas, $contentType = Validator::SCHEMA_MEDIA_TYPE)
    {
        $this->schemas     = $schemas;
        $this->contentType = $contentType;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve($uri)
    {
        if (!array_key_exists($uri, $this->schemas)) {
            throw new ResourceNotFoundException(sprintf(
                'The JSON schema "%s" was not found.',
                $uri
            ));
        }

        return $this->schemas[$uri];
    }

    /**
     * {@inheritDoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
