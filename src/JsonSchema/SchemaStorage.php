<?php

declare(strict_types=1);

namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\UnresolvableJsonPointerException;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;

class SchemaStorage implements SchemaStorageInterface
{
    public const INTERNAL_PROVIDED_SCHEMA_URI = 'internal://provided-schema/';

    /**
     * Keywords whose value is a map of subschemas keyed by an arbitrary name, so a member
     * named after a keyword is a schema rather than that keyword's value.
     */
    private const SCHEMA_MAP_KEYWORDS = [
        'properties',
        'patternProperties',
        'definitions',
        '$defs',
        'dependencies',
        'dependentSchemas',
    ];

    protected $uriRetriever;
    protected $uriResolver;
    protected $schemas = [];

    public function __construct(
        ?UriRetrieverInterface $uriRetriever = null,
        ?UriResolverInterface $uriResolver = null
    ) {
        $this->uriRetriever = $uriRetriever ?: new UriRetriever();
        $this->uriResolver = $uriResolver ?: new UriResolver();
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
     * {@inheritdoc}
     */
    public function addSchema(string $id, $schema = null): void
    {
        if (is_null($schema) && $id !== self::INTERNAL_PROVIDED_SCHEMA_URI) {
            // if the schema was user-provided to Validator and is still null, then assume this is
            // what the user intended, as there's no way for us to retrieve anything else. User-supplied
            // schemas do not have an associated URI when passed via Validator::validate().
            $schema = $this->uriRetriever->retrieve($id);
        }

        // cast array schemas to object
        if (is_array($schema)) {
            $schema = BaseConstraint::arrayToObjectRecursive($schema);
        }

        // workaround for bug in draft-03 & draft-04 meta-schemas (id & $ref defined with incorrect format)
        // see https://github.com/json-schema-org/JSON-Schema-Test-Suite/issues/177#issuecomment-293051367
        if (is_object($schema) && property_exists($schema, 'id')) {
            if ($schema->id === DraftIdentifiers::DRAFT_4) {
                $schema->properties->id->format = 'uri-reference';
            } elseif ($schema->id === DraftIdentifiers::DRAFT_3) {
                $schema->properties->id->format = 'uri-reference';
                $schema->properties->{'$ref'}->format = 'uri-reference';
            }
        }

        // an id on the document root changes the base uri for the whole document
        $baseId = $id;
        if (is_object($schema)) {
            $rootId = $this->findSchemaIdInObject($schema);
            if (is_string($rootId)) {
                $baseId = $this->uriResolver->resolve($rootId, $id);
            }
        }

        $this->scanForSubschemas($schema, $baseId);

        // resolve references
        $this->expandRefs($schema, $id);

        $this->schemas[$id] = $schema;
    }

    /**
     * Recursively resolve all references against the provided base
     *
     * @param mixed        $schema
     * @param list<string> $propertyStack
     */
    private function expandRefs(&$schema, ?string $parentId = null, array $propertyStack = []): void
    {
        if (!is_object($schema)) {
            if (is_array($schema)) {
                foreach ($schema as &$member) {
                    $this->expandRefs($member, $parentId);
                }
            }

            return;
        }

        if (property_exists($schema, '$ref') && is_string($schema->{'$ref'})) {
            $refPointer = new JsonPointer($this->uriResolver->resolve($schema->{'$ref'}, $parentId));
            $schema->{'$ref'} = (string) $refPointer;
        }

        $parentProperty = array_slice($propertyStack, -1)[0] ?? '';
        foreach ($schema as $propertyName => &$member) {
            if ($parentProperty !== 'properties' && in_array($propertyName, ['enum', 'const'])) {
                // Enum and const don't allow $ref as a keyword, see https://github.com/json-schema-org/JSON-Schema-Test-Suite/pull/445
                continue;
            }

            $schemaId = $this->findSchemaIdInObject($schema);
            $childId = $parentId;
            if (is_string($schemaId) && $childId !== $schemaId) {
                $childId = $this->uriResolver->resolve($schemaId, $childId);
            }

            $clonedPropertyStack = $propertyStack;
            $clonedPropertyStack[] = $propertyName;
            $this->expandRefs($member, $childId, $clonedPropertyStack);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema(string $id)
    {
        if (!array_key_exists($id, $this->schemas)) {
            $this->addSchema($id);
        }

        return $this->schemas[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function resolveRef(string $ref, $resolveStack = [])
    {
        $jsonPointer = new JsonPointer($ref);

        // resolve filename for pointer
        $fileName = $jsonPointer->getFilename();
        if (!strlen($fileName)) {
            throw new UnresolvableJsonPointerException(sprintf(
                "Could not resolve fragment '%s': no file is defined",
                $jsonPointer->getPropertyPathAsString()
            ));
        }

        // get & process the schema
        $refSchema = $this->getSchema($fileName);
        foreach ($jsonPointer->getPropertyPaths() as $path) {
            $path = urldecode($path);
            if (is_object($refSchema) && property_exists($refSchema, $path)) {
                $refSchema = $this->resolveRefSchema($refSchema->{$path}, $resolveStack);
            } elseif (is_array($refSchema) && array_key_exists($path, $refSchema)) {
                $refSchema = $this->resolveRefSchema($refSchema[$path], $resolveStack);
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
     * {@inheritdoc}
     */
    public function resolveRefSchema($refSchema, $resolveStack = [])
    {
        if (is_object($refSchema) && property_exists($refSchema, '$ref') && is_string($refSchema->{'$ref'})) {
            if (in_array($refSchema, $resolveStack, true)) {
                throw new UnresolvableJsonPointerException(sprintf(
                    'Dereferencing a pointer to %s results in an infinite loop',
                    $refSchema->{'$ref'}
                ));
            }
            $resolveStack[] = $refSchema;

            return $this->resolveRef($refSchema->{'$ref'}, $resolveStack);
        }

        if (is_object($refSchema) && array_keys(get_object_vars($refSchema)) === ['']) {
            $refSchema = get_object_vars($refSchema)[''];
        }

        return $refSchema;
    }

    /**
     * @param mixed $schema
     */
    private function scanForSubschemas($schema, string $parentId, string $parentProperty = ''): void
    {
        if (!$schema instanceof \stdClass  && !is_array($schema)) {
            return;
        }

        foreach ($schema as $propertyName => $potentialSubSchema) {
            // Enum and const don't allow id as a keyword, see https://github.com/json-schema-org/JSON-Schema-Test-Suite/pull/471
            // Their values are skipped entirely, but a subschema may legitimately be named
            // 'enum' or 'const', so the enclosing keyword decides which of the two this is.
            if (
                in_array($propertyName, ['enum', 'const'], true)
                && !in_array($parentProperty, self::SCHEMA_MAP_KEYWORDS, true)
            ) {
                continue;
            }

            if (is_array($potentialSubSchema)) {
                foreach ($potentialSubSchema as $potentialSubSchemaItem) {
                    $this->scanSubschema($potentialSubSchemaItem, $parentId, (string) $propertyName);
                }
                continue;
            }

            $this->scanSubschema($potentialSubSchema, $parentId, (string) $propertyName);
        }
    }

    /**
     * Register a subschema under the base uri established by its own id, if it has one, and
     * continue scanning below it against that base uri.
     *
     * @param mixed $subSchema
     */
    private function scanSubschema($subSchema, string $parentId, string $parentProperty): void
    {
        if (!is_object($subSchema)) {
            $this->scanForSubschemas($subSchema, $parentId, $parentProperty);

            return;
        }

        $childId = $parentId;
        $subSchemaId = $this->findSchemaIdInObject($subSchema);
        if (is_string($subSchemaId)) {
            // An id nested in the document changes the base uri for everything below it
            $childId = $this->uriResolver->resolve($subSchemaId, $parentId);

            if (property_exists($subSchema, 'type')) {
                // Found sub schema. It is registered directly rather than through addSchema(),
                // which would resolve the already resolved id against itself a second time.
                $this->schemas[$childId] = $subSchema;
            }
        }

        $this->scanForSubschemas($subSchema, $childId, $parentProperty);
    }

    private function findSchemaIdInObject(object $schema): ?string
    {
        if (property_exists($schema, 'id') && is_string($schema->id)) {
            return $schema->id;
        }
        if (property_exists($schema, '$id') && is_string($schema->{'$id'})) {
            return $schema->{'$id'};
        }

        return null;
    }
}
