<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Exception\JsonDecodingException;
use JsonSchema\Uri\Retrievers\UriRetrieverInterface;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Uri\UriResolver;

/**
 * Take in an object that's a JSON schema and take care of all $ref references
 *
 * @author Tyler Akins <fidian@rumkin.com>
 * @see    README.md
 */
class RefResolver
{
    /**
     * @var UriRetrieverInterface
     */
    protected $uriRetriever = null;

    /**
     * @var array
     */
    protected $schemas = array();

    /**
     * @var array
     */
    protected $scopes = array();

    /**
     * @param UriRetriever $retriever
     */
    public function __construct($retriever = null)
    {
        $this->uriRetriever = $retriever;
    }

    /**
     * Retrieves a given schema given a ref and a source URI
     *
     * @param  string $ref       Reference from schema
     * @param  string $sourceUri URI where original schema was located
     * @return object            Schema
     */
    public function fetchRef($ref, $sourceUri)
    {
        // Get absolute uri
        $resolver = new UriResolver();
        $uri = $resolver->resolve($ref, $sourceUri);

        // Split in to location and fragment
        $location = $resolver->extractLocation($uri);
        $fragment = $resolver->extractFragment($uri);

        // Retrieve dereferenced schema
        if ($location == null) {
            $schema = end($this->schemas);
        } elseif (array_key_exists($location, $this->schemas)) {
            $schema = $this->schemas[$location];
        } else {
            $retriever = $this->getUriRetriever();
            $schema = $retriever->retrieve($location);

            $this->schemas[$location] = $schema;
            $this->resolve($schema, $location);
        }

        // Resolve JSON pointer
        $retriever = $this->getUriRetriever();
        $object = $retriever->resolvePointer($schema, $fragment);

        if ($object instanceof \stdClass) {
            $object->id = $uri;
        }

        return $object;
    }

    /**
     * Return the URI Retriever, defaulting to making a new one if one
     * was not yet set.
     *
     * @return UriRetriever
     */
    public function getUriRetriever()
    {
        if (is_null($this->uriRetriever)) {
            $this->setUriRetriever(new UriRetriever);
        }

        return $this->uriRetriever;
    }

    /**
     * Resolves all $ref references for a given schema.  Recurses through
     * the object to resolve references of any child schemas.
     *
     * The 'format' property is omitted because it isn't required for
     * validation.  Theoretically, this class could be extended to look
     * for URIs in formats: "These custom formats MAY be expressed as
     * an URI, and this URI MAY reference a schema of that format."
     *
     * The 'id' property is not filled in, but that could be made to happen.
     *
     * @param object $schema    JSON Schema to flesh out
     * @param string $sourceUri URI where this schema was located
     */
    public function resolve($schema, $sourceUri = null)
    {
        if (!is_object($schema)) {
            return;
        }

        // Fill in id property
        if ($sourceUri) {
            $schema->id = $sourceUri;
        }

        // First determine our resolution scope
        $scope = $this->getResolutionScope($schema, $sourceUri);

        // These properties are just schemas
        // eg.  items can be a schema or an array of schemas
        foreach (array('additionalItems', 'additionalProperties', 'extends', 'items') as $propertyName) {
            $this->resolveProperty($schema, $propertyName, $scope);
        }

        // These are all potentially arrays that contain schema objects
        // eg.  type can be a value or an array of values/schemas
        // eg.  items can be a schema or an array of schemas
        foreach (array('disallow', 'extends', 'items', 'type', 'allOf', 'anyOf', 'oneOf') as $propertyName) {
            $this->resolveArrayOfSchemas($schema, $propertyName, $scope);
        }

        // These are all objects containing properties whose values are schemas
        foreach (array('definitions', 'dependencies', 'patternProperties', 'properties') as $propertyName) {
            $this->resolveObjectOfSchemas($schema, $propertyName, $scope);
        }

        // Resolve $ref
        $this->resolveRef($schema, $scope);

        // Pop back out of our scope
        array_pop($this->scopes);
    }

    /**
     * Returns the resolution scope for the given schema. Inspects the partial
     * for the presence of 'id' and then returns that as a absolute uri.
     *
     * @param  object $schemaPartial JSON Schema to get the resolution scope for
     * @param  string $sourceUri     URI where this schema was located
     * @return string
     */
    private function getResolutionScope($schemaPartial, $sourceUri)
    {
        if (count($this->scopes) === 0) {
            $this->scopes[] = '#';
            $this->schemas[] = $schemaPartial;
        }

        if (!empty($schemaPartial->id)) {
            $resolver = new UriResolver();
            $this->scopes[] = $resolver->resolve($schemaPartial->id, $sourceUri);
        } else {
            $this->scopes[] = end($this->scopes);
        }

        return end($this->scopes);
    }

    /**
     * Given an object and a property name, that property should be an
     * array whose values can be schemas.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
     */
    public function resolveArrayOfSchemas($schema, $propertyName, $sourceUri)
    {
        if (! isset($schema->$propertyName) || ! is_array($schema->$propertyName)) {
            return;
        }

        foreach ($schema->$propertyName as $possiblySchema) {
            $this->resolve($possiblySchema, $sourceUri);
        }
    }

    /**
     * Given an object and a property name, that property should be an
     * object whose properties are schema objects.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
     */
    public function resolveObjectOfSchemas($schema, $propertyName, $sourceUri)
    {
        if (! isset($schema->$propertyName) || ! is_object($schema->$propertyName)) {
            return;
        }

        foreach (get_object_vars($schema->$propertyName) as $possiblySchema) {
            $this->resolve($possiblySchema, $sourceUri);
        }
    }

    /**
     * Given an object and a property name, that property should be a
     * schema object.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
     */
    public function resolveProperty($schema, $propertyName, $sourceUri)
    {
        if (! isset($schema->$propertyName)) {
            return;
        }

        $this->resolve($schema->$propertyName, $sourceUri);
    }

    /**
     * Look for the $ref property in the object.  If found, remove the
     * reference and augment this object with the contents of another
     * schema.
     *
     * @param object $schema    JSON Schema to flesh out
     * @param string $sourceUri URI where this schema was located
     */
    public function resolveRef($schema, $sourceUri)
    {
        $ref = '$ref';

        if (empty($schema->$ref)) {
            return;
        }

        // Retrieve the referenced schema
        $uri = $schema->$ref;
        $refSchema = $this->fetchRef($schema->$ref, $sourceUri, $schema);

        // Remove the reference node
        unset($schema->$ref);

        // Augment the properties (FIXME this is a bit naive and might need fixing)
        foreach (get_object_vars($refSchema) as $prop => $value) {
            $schema->$prop = $value;
        }

        // Check for nested references
        $this->resolveRef($schema, $sourceUri);
    }

    /**
     * Set URI Retriever for use with the Ref Resolver
     *
     * @param UriRetriever $retriever
     * @return $this for chaining
     */
    public function setUriRetriever(UriRetriever $retriever)
    {
        $this->uriRetriever = $retriever;

        return $this;
    }

    protected function resolveRefSegment($data, $pathParts)
    {
        foreach ($pathParts as $path) {
            $path = strtr($path, array('~1' => '/', '~0' => '~', '%25' => '%'));

            if (is_array($data)) {
                $data = $data[$path];
            } else {
                $data = $data->{$path};
            }
        }

        return $data;
    }
}
