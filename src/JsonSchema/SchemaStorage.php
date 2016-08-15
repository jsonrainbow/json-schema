<?php

namespace JsonSchema;

use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\UnresolvableJsonPointerException;
use JsonSchema\Iterator\ObjectIterator;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;

class SchemaStorage
{
    protected $uriRetriever;
    protected $uriResolver;
    protected $schemas = array();

    public function __construct(
        UriRetrieverInterface $uriRetriever = null,
        UriResolverInterface $uriResolver = null
    ) {
        $this->uriRetriever = $uriRetriever ?: new UriRetriever;
        $this->uriResolver = $uriResolver ?: new UriResolver;
    }

    /**
     * @return UriRetrieverInterface
     */
    public function getUriRetriever()
    {
        return $this->uriRetriever;
    }

    /**
     * @return UriResolverInterface
     */
    public function getUriResolver()
    {
        return $this->uriResolver;
    }

    /**
     * @param string $id
     * @param object $schema
     */
    public function addSchema($id, $schema = null)
    {
        if (is_null($schema)) {
            $schema = $this->uriRetriever->retrieve($id);
        }
        $objectIterator = new ObjectIterator($schema);
        foreach ($objectIterator as $toResolveSchema) {
            if (property_exists($toResolveSchema, '$ref') && is_string($toResolveSchema->{'$ref'})) {
                $jsonPointer = new JsonPointer($this->uriResolver->resolve($toResolveSchema->{'$ref'}, $id));
                $toResolveSchema->{'$ref'} = (string)$jsonPointer;
            }
        }
        $this->schemas[$id] = $schema;
    }

    /**
     * @param string $id
     * @return object
     */
    public function getSchema($id)
    {
        if (!array_key_exists($id, $this->schemas)) {
            $this->addSchema($id);
        }

        return $this->schemas[$id];
    }

    public function resolveRef($ref)
    {
        $jsonPointer = new JsonPointer($ref);
        $refSchema = $this->getSchema($jsonPointer->getFilename());

        foreach ($jsonPointer->getPropertyPaths() as $path) {
            if (is_object($refSchema) && property_exists($refSchema, $path)) {
                $refSchema = $this->resolveRefSchema($refSchema->{$path});
            } elseif (is_array($refSchema) && array_key_exists($path, $refSchema)) {
                $refSchema = $this->resolveRefSchema($refSchema[$path]);
            } else {
                throw new UnresolvableJsonPointerException(sprintf(
                    'File: %s is found, but could not resolve fragment: %s',
                    $jsonPointer->getFilename(),
                    $jsonPointer->getPropertyPathAsString()
                ));
            }
        }

        return $refSchema;
    }

    /**
     * @param $refSchema
     * @return object
     */
    public function resolveRefSchema($refSchema)
    {
        if (is_object($refSchema) && property_exists($refSchema, '$ref')) {
            $newSchema = $this->resolveRef($refSchema->{'$ref'});
            $refSchema = (object) (get_object_vars($refSchema) + get_object_vars($newSchema));
            unset($refSchema->{'$ref'});
        }

        return $refSchema;
    }
}
