<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Uri\Retrievers\UriRetrieverInterface;

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
        $retriever = $this->getUriRetriever();
        $jsonSchema = $retriever->retrieve($ref, $sourceUri);
        $this->resolve($jsonSchema);

        return $jsonSchema;
    }

    /**
     * Return the URI Retriever, defaulting to making a new one if one
     * was not yet set.
     *
     * @return Uri\UriRetriever
     */
    public function getUriRetriever()
    {
        if (is_null($this->uriRetriever)) {
            $this->setUriRetriever(new Uri\UriRetriever);
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
        if (! is_object($schema)) {
            return;
        }

        if (!empty($schema->id)) {
            $sourceUri = $this->getUriRetriever()->resolve($schema->id, $sourceUri);
        }

        // Resolve $ref first
        $this->resolveRef($schema, $sourceUri);
        // Resolve extends first
        $this->resolveExtends($schema, $sourceUri);


        // These properties are just schemas
        // eg.  items can be a schema or an array of schemas
        foreach (array('additionalItems', 'additionalProperties', 'extends', 'items') as $propertyName) {
            $this->resolveProperty($schema, $propertyName, $sourceUri);
        }

        // These are all potentially arrays that contain schema objects
        // eg.  type can be a value or an array of values/schemas
        // eg.  items can be a schema or an array of schemas
        foreach (array('disallow', 'extends', 'items', 'type') as $propertyName) {
            $this->resolveArrayOfSchemas($schema, $propertyName, $sourceUri);
        }

        // These are all objects containing properties whose values are schemas
        foreach (array('dependencies', 'patternProperties', 'properties') as $propertyName) {
            $this->resolveObjectOfSchemas($schema, $propertyName, $sourceUri);
        }
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

        $refSchema = $this->fetchRef($schema->$ref, $sourceUri);
        unset($schema->$ref);

        // Augment the current $schema object with properties fetched
        foreach (get_object_vars($refSchema) as $prop => $value) {
            $schema->$prop = $value;
        }
    }

    /**
     * Look for the $ref property in the object.  If found, remove the
     * reference and augment this object with the contents of another
     * schema.
     *
     * @param object $schema    JSON Schema to flesh out
     * @param string $sourceUri URI where this schema was located
     */
    public function resolveExtends($schema, $sourceUri)
    {
        if (empty($schema->extends)) {
            return;
        }
        if(is_object($schema->extends)) {
            self::merge($schema, $schema->extends);
        } else {
            if(is_array($schema->extends)) {
                foreach($schema->extends as $extends) {
                    // yeah, some copy paste here
                    if(is_object($schema)) {
                        self::merge($schema, $schema);
                    } else {
                        $refSchema = $this->fetchRef($extends, $sourceUri);
                        $refSchema = $this->getUriRetriever()->resolvePointer($refSchema, $extends);
                        self::merge($schema, $refSchema);
                    }
                }
            } else {
                $refSchema = $this->fetchRef($schema->extends, $sourceUri);
                $refSchema = $this->getUriRetriever()->resolvePointer($refSchema, $schema->extends);
                self::merge($schema, $refSchema);
            }
        }
        unset($schema->extends);
    }

    /**
    * recursively merges all fields of $b into $a
    * fields in a win
    * @param stdObject $a
    * @param stdObject $b
    * @return void
    */
    static function merge($a, $b) {
        if(!$a) return;
        if(!$b) return;
        foreach($b as $k=>$v) {
            if(is_object($v)) {
                if(!isset($a->$k)) $a->$k = new \stdClass;
                self::merge($a->$k, $b->$k);
            } elseif(is_array($v)) {
                if(!isset($a->$k)) {
                    $a->$k = $b->$k;
                } elseif(is_array($a->$k)) {
                    $a->$k = array_merge_recursive($b->$k, $a->$k); // a should win
                }
            } else {
                if(isset($a->$k)) continue; // a should win
                $a->$k = $b->$k;
            }
        }
    }

    /**
     * Set URI Retriever for use with the Ref Resolver
     *
     * @param Uri\UriRetriever $retriever
     * @return $this for chaining
     */
    public function setUriRetriever(Uri\UriRetriever $retriever)
    {
        $this->uriRetriever = $retriever;

        return $this;
    }
}
