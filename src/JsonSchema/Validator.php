<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Constraints\Schema;
use JsonSchema\Constraints\Constraint;

use JsonSchema\Exception\InvalidSchemaMediaTypeException;
use JsonSchema\Exception\JsonDecodingException;

use JsonSchema\Uri\Retrievers\UriRetrieverInterface;

/**
 * A JsonSchema Constraint
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 * @see    README.md
 */
class Validator extends Constraint
{
    const SCHEMA_MEDIA_TYPE = 'application/schema+json';
    
    private static $uriRetriever;

    /**
     * Validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org
     *
     * {@inheritDoc}
     */
    public function check($value, $schema = null, $path = null, $i = null)
    {
        $validator = new Schema($this->checkMode);
        $validator->check($value, $schema);

        $this->addErrors($validator->getErrors());
    }
    
    /**
     * Sets the URI retriever the validator will use. FileGetContents by default
     * 
     * @param UriRetrieverInterface $retriever
     */
    public static function setUriRetriever(UriRetrieverInterface $retriever)
    {
        self::$uriRetriever = $retriever;
    }
    
    /**
     * @param string $uri JSON Schema URI
     * @return string JSON Schema contents
     * @throws InvalidSchemaMediaType for invalid media types
     */
    public static function retrieveUri($uri)
    {
        if (null === self::$uriRetriever) {
            self::setUriRetriever(new Uri\Retrievers\FileGetContents);
        }
        $contents = self::$uriRetriever->retrieve($uri);
        if (self::SCHEMA_MEDIA_TYPE !== self::$uriRetriever->getContentType()) {
            throw new InvalidSchemaMediaTypeException(sprintf('Media type %s expected', self::SCHEMA_MEDIA_TYPE));
        }
        $jsonSchema = json_decode($contents);
        if (JSON_ERROR_NONE < $error = json_last_error()) {
            throw new JsonDecodingException($error);
        }
        
        // TODO validate using schema)
        $jsonSchema->_id = $uri;
        return $jsonSchema;
    }
}