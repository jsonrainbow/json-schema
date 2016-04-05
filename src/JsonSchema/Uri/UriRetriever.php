<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Uri;

use JsonSchema\PointerResolver;
use JsonSchema\Uri\Retrievers\FileGetContents;
use JsonSchema\Uri\Retrievers\UriRetrieverInterface;
use JsonSchema\Validator;
use JsonSchema\Exception\InvalidSchemaMediaTypeException;
use JsonSchema\Exception\JsonDecodingException;
use JsonSchema\Exception\ResourceNotFoundException;

/**
 * Retrieves JSON Schema URIs
 *
 * @author Tyler Akins <fidian@rumkin.com>
 */
class UriRetriever
{
    /**
     * @var null|UriRetrieverInterface
     */
    protected $uriRetriever = null;

    /**
     * @var array|object[]
     * @see loadSchema
     */
    private $schemaCache = array();

    /**
     * Guarantee the correct media type was encountered
     *
     * @param UriRetrieverInterface $uriRetriever
     * @param string $uri
     * @return bool|void
     */
    public function confirmMediaType($uriRetriever, $uri)
    {
        $contentType = $uriRetriever->getContentType();

        if (is_null($contentType)) {
            // Well, we didn't get an invalid one
            return;
        }

        if (Validator::SCHEMA_MEDIA_TYPE === $contentType) {
            return;
        }

        if (substr($uri, 0, 23) == 'http://json-schema.org/') {
            //HACK; they deliver broken content types
            return true;
        }

        throw new InvalidSchemaMediaTypeException(sprintf('Media type %s expected', Validator::SCHEMA_MEDIA_TYPE));
    }

    /**
     * Get a URI Retriever
     *
     * If none is specified, sets a default FileGetContents retriever and
     * returns that object.
     *
     * @return UriRetrieverInterface
     */
    public function getUriRetriever()
    {
        if (is_null($this->uriRetriever)) {
            $this->setUriRetriever(new FileGetContents);
        }

        return $this->uriRetriever;
    }

    /**
     * Resolve a schema based on pointer
     *
     * URIs can have a fragment at the end in the format of
     * #/path/to/object and we are to look up the 'path' property of
     * the first object then the 'to' and 'object' properties.
     *
     * @param object $jsonSchema JSON Schema contents
     * @param string $uri JSON Schema URI
     * @return object JSON Schema after walking down the fragment pieces
     *
     * @throws ResourceNotFoundException
     */
    public function resolvePointer($jsonSchema, $uri)
    {
        $resolver = new UriResolver();
        $parsed = $this->parse($uri);
        if (empty($parsed['fragment'])) {
            return $jsonSchema;
        }

        $pointerResolver = new PointerResolver();

        $reference = $pointerResolver->resolvePointer($jsonSchema, $parsed['fragment']);

        if (!is_object($reference)) {
            throw new ResourceNotFoundException("Pointer was not an object");
        }

        return $reference;
    }

    /**
     * Retrieve a URI
     *
     * @param string $uri JSON Schema URI
     * @param string|null $baseUri
     * @return object JSON Schema contents
     */
    public function retrieve($uri, $baseUri = null)
    {
        $resolver = new UriResolver();
        $resolvedUri = $fetchUri = $resolver->resolve($uri, $baseUri);

        //fetch URL without #fragment
        $arParts = $this->parse($resolvedUri);
        if (isset($arParts['fragment'])) {
            unset($arParts['fragment']);
            $fetchUri = $this->generate($arParts);
        }

        $jsonSchema = $this->loadSchema($fetchUri);

        // Use the JSON pointer if specified
        $jsonSchema = $this->resolvePointer($jsonSchema, $resolvedUri);

        if ($jsonSchema instanceof \stdClass) {
            $jsonSchema->id = $resolvedUri;
        }

        return $jsonSchema;
    }

    /**
     * Fetch a schema from the given URI, json-decode it and return it.
     * Caches schema objects.
     *
     * @param string $fetchUri Absolute URI
     *
     * @return object JSON schema object
     */
    protected function loadSchema($fetchUri)
    {
        if (isset($this->schemaCache[$fetchUri])) {
            return $this->schemaCache[$fetchUri];
        }

        $uriRetriever = $this->getUriRetriever();
        $contents = $this->uriRetriever->retrieve($fetchUri);
        $this->confirmMediaType($uriRetriever, $fetchUri);
        $jsonSchema = json_decode($contents);

        if (JSON_ERROR_NONE < $error = json_last_error()) {
            throw new JsonDecodingException($error);
        }

        $this->schemaCache[$fetchUri] = $jsonSchema;

        return $jsonSchema;
    }

    /**
     * Set the URI Retriever
     *
     * @param UriRetrieverInterface $uriRetriever
     * @return $this for chaining
     */
    public function setUriRetriever(UriRetrieverInterface $uriRetriever)
    {
        $this->uriRetriever = $uriRetriever;

        return $this;
    }

    /**
     * Parses a URI into five main components
     *
     * @param string $uri
     * @return array
     */
    public function parse($uri)
    {
        preg_match('|^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?|', $uri, $match);

        $components = array();
        if (5 < count($match)) {
            $components =  array(
                'scheme'    => $match[2],
                'authority' => $match[4],
                'path'      => $match[5]
            );
        }

        if (7 < count($match)) {
            $components['query'] = $match[7];
        }

        if (9 < count($match)) {
            $components['fragment'] = $match[9];
        }

        return $components;
    }

    /**
     * Builds a URI based on n array with the main components
     *
     * @param array $components
     * @return string
     */
    public function generate(array $components)
    {
        $uri = $components['scheme'] . '://'
             . $components['authority']
             . $components['path'];

        if (array_key_exists('query', $components)) {
            $uri .= $components['query'];
        }

        if (array_key_exists('fragment', $components)) {
            $uri .= $components['fragment'];
        }

        return $uri;
    }

    /**
     * @param string $uri
     * @return boolean
     */
    public function isValid($uri)
    {
        $components = $this->parse($uri);

        return !empty($components);
    }
}
