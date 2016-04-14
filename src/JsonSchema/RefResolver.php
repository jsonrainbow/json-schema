<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Exception\UnresolvableJsonPointerException;
use JsonSchema\Iterator\ObjectIterator;
use JsonSchema\Entity\JsonPointer;

/**
 * Take in a source uri to locate a JSON schema and retrieve it and take care of all $ref references.
 * Try to update the resolved schema which looks like a tree, but can be a graph. (so cyclic schema's are allowed).
 * This way the current validator does not need to be changed and can work as well with the updated schema.
 *
 * @package JsonSchema
 * @author Joost Nijhuis <jnijhuis81@gmail.com>
 * @author Rik Jansen <rikjansen@gmail.com>
 */
class RefResolver
{
    /** @var UriRetrieverInterface */
    private $uriRetriever;

    /** @var UriResolverInterface */
    private $uriResolver;

    /**
     * @param UriRetrieverInterface $retriever
     * @param UriResolverInterface $uriResolver
     */
    public function __construct(UriRetrieverInterface $retriever, UriResolverInterface $uriResolver)
    {
        $this->uriRetriever = $retriever;
        $this->uriResolver = $uriResolver;
    }

    /**
     * Resolves all schema and all $ref references for the give $sourceUri. Recurse through the object to resolve
     * references of any child schemas and return the schema.
     *
     * @param string $sourceUri URI where this schema was located
     * @return object
     */
    public function resolve($sourceUri)
    {
        return $this->resolveCached($sourceUri, array());
    }

    /**
     * @param string $sourceUri URI where this schema was located
     * @param array $paths
     * @return object
     */
    private function resolveCached($sourceUri, array $paths)
    {
        $jsonPointer = new JsonPointer($sourceUri);

        $fileName = $jsonPointer->getFilename();
        if (!array_key_exists($fileName, $paths)) {
            $schema = $this->uriRetriever->retrieve($jsonPointer->getFilename());
            $paths[$jsonPointer->getFilename()] = $schema;
            $this->resolveSchemas($schema, $jsonPointer->getFilename(), $paths);
        }
        $schema = $paths[$fileName];

        return $this->getRefSchema($jsonPointer, $schema);
    }

    /**
     * Recursive resolve schema by traversing through al nodes
     *
     * @param object $unresolvedSchema
     * @param string $fileName
     * @param array $paths
     */
    private function resolveSchemas($unresolvedSchema, $fileName, array $paths)
    {
        $objectIterator = new ObjectIterator($unresolvedSchema);
        foreach ($objectIterator as $toResolveSchema) {
            if (property_exists($toResolveSchema, '$ref') && is_string($toResolveSchema->{'$ref'})) {
                $jsonPointer = new JsonPointer($this->uriResolver->resolve($toResolveSchema->{'$ref'}, $fileName));
                $refSchema = $this->resolveCached((string) $jsonPointer, $paths);
                $this->unionSchemas($refSchema, $toResolveSchema, $fileName, $paths);
            }
        }
    }

    /**
     * @param JsonPointer $jsonPointer
     * @param object $refSchema
     * @throws UnresolvableJsonPointerException when json schema file is found but reference can not be resolved
     * @return object
     */
    private function getRefSchema(JsonPointer $jsonPointer, $refSchema)
    {
        foreach ($jsonPointer->getPropertyPaths() as $path) {
            if (is_object($refSchema) && property_exists($refSchema, $path)) {
                $refSchema = $refSchema->{$path};
            } elseif (is_array($refSchema) && array_key_exists($path, $refSchema)) {
                $refSchema = $refSchema[$path];
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
     * @param object $refSchema
     * @param object $schema
     * @param string $fileName
     * @param array $paths
     */
    private function unionSchemas($refSchema, $schema, $fileName, array $paths)
    {
        if (property_exists($refSchema, '$ref')) {
            $jsonPointer = new JsonPointer($this->uriResolver->resolve($refSchema->{'$ref'}, $fileName));
            $newSchema = $this->resolveCached((string) $jsonPointer, $paths);
            $this->unionSchemas($newSchema, $refSchema, $fileName, $paths);
        }

        unset($schema->{'$ref'});
        if (!$this->hasSubSchemas($schema)) {
            foreach (get_object_vars($refSchema) as $prop => $value) {
                $schema->$prop = $value;
            }
        } else {
            $newSchema = new \stdClass();
            foreach (get_object_vars($schema) as $prop => $value) {
                $newSchema->$prop = $value;
                unset($schema->$prop);
            }
            $schema->allOf = array($newSchema, $refSchema);
        }
    }

    /**
     * @param object $schema
     * @return bool
     */
    private function hasSubSchemas($schema)
    {
        foreach (array_keys(get_object_vars($schema)) as $propertyName) {
            if (in_array($propertyName, $this->getReservedKeysWhichAreInFactSubSchemas())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getReservedKeysWhichAreInFactSubSchemas()
    {
        return array(
            'additionalItems',
            'additionalProperties',
            'extends',
            'items',
            'disallow',
            'extends',
            'items',
            'type',
            'allOf',
            'anyOf',
            'oneOf',
            'dependencies',
            'patternProperties',
            'properties'
        );
    }
}
