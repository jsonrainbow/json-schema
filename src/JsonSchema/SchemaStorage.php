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
     * Keywords whose value is a schema.
     *
     * 'items' and 'extends' also appear below, as they hold either a schema or a list of schemas.
     */
    private const SCHEMA_KEYWORDS = [
        'additionalItems',
        'additionalProperties',
        'contains',
        'contentSchema',
        'else',
        'extends',
        'if',
        'items',
        'not',
        'propertyNames',
        'then',
        'unevaluatedItems',
        'unevaluatedProperties',
    ];

    /**
     * Keywords whose value is a list of schemas.
     *
     * Draft 3's 'disallow' and 'type' take a list mixing type names and schemas: only the
     * schemas among them are objects, so the type names are skipped like any other scalar.
     */
    private const SCHEMA_ARRAY_KEYWORDS = [
        'allOf',
        'anyOf',
        'disallow',
        'extends',
        'items',
        'oneOf',
        'prefixItems',
        'type',
    ];

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

    /**
     * Schemas named by a plain-name fragment, keyed by the absolute uri including that fragment:
     * a fragment-only id in draft-04 to draft-07, or an $anchor in draft 2019-09.
     *
     * @var array<string, object>
     */
    private $anchors = [];

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
            $baseId = $this->registerIdentifiers($schema, $id);
        }

        $this->scanMembersForSubschemas($schema, $baseId);

        // resolve references
        $this->expandRefs($schema, $id);

        $this->schemas[$id] = $schema;
    }

    /**
     * Recursively resolve all references against the provided base
     *
     * $refSeesSiblingId tells whether a $ref is resolved against an id next to it. Since draft
     * 2019-09 it is, while up to draft-07 a $ref makes its siblings ignored, the id among them.
     *
     * @param mixed        $schema
     * @param list<string> $propertyStack
     */
    private function expandRefs(&$schema, ?string $parentId = null, array $propertyStack = [], bool $refSeesSiblingId = false): void
    {
        if (!is_object($schema)) {
            if (is_array($schema)) {
                foreach ($schema as &$member) {
                    $this->expandRefs($member, $parentId, [], $refSeesSiblingId);
                }
            }

            return;
        }

        if (property_exists($schema, '$schema') && is_string($schema->{'$schema'})) {
            $refSeesSiblingId = in_array(
                rtrim($schema->{'$schema'}, '#'),
                [DraftIdentifiers::DRAFT_2019_09, DraftIdentifiers::DRAFT_2020_12],
                true
            );
        }

        $childId = is_string($parentId) ? $this->baseUriOf($schema, $parentId) : $parentId;

        if (property_exists($schema, '$ref') && is_string($schema->{'$ref'})) {
            $ref = $this->uriResolver->resolve($schema->{'$ref'}, $refSeesSiblingId ? $childId : $parentId);
            if (!$this->isAnchorReference($ref)) {
                $ref = (string) new JsonPointer($ref);
            }
            $schema->{'$ref'} = $ref;
        }

        $parentProperty = array_slice($propertyStack, -1)[0] ?? '';
        foreach ($schema as $propertyName => &$member) {
            // A $ref is only a reference inside a schema, so data positions such as 'enum',
            // 'const' or 'default' are left untouched, see https://github.com/json-schema-org/JSON-Schema-Test-Suite/pull/445
            if (!self::holdsSchemas((string) $propertyName, $parentProperty)) {
                continue;
            }

            $clonedPropertyStack = $propertyStack;
            $clonedPropertyStack[] = $propertyName;
            $this->expandRefs($member, $childId, $clonedPropertyStack, $refSeesSiblingId);
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
        if ($this->isAnchorReference($ref)) {
            return $this->resolveAnchor($ref, $resolveStack);
        }

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
     * Walk the members of one container - a schema object, or an array such as allOf or a
     * tuple items - handing each member to registerAndScanSubschema().
     *
     * $parentProperty is the keyword the container itself sits under: inside a schema map, every
     * member is a schema whatever its name, see holdsSchemas().
     *
     * @param mixed $schema
     */
    private function scanMembersForSubschemas($schema, string $parentId, string $parentProperty = ''): void
    {
        if (!$schema instanceof \stdClass  && !is_array($schema)) {
            return;
        }

        foreach ($schema as $propertyName => $member) {
            // An id only identifies a schema, so only positions holding schemas are descended
            // into. Data positions such as 'default', 'examples', 'enum', 'const' and any unknown
            // keyword are left alone, and an id written there identifies nothing,
            // see https://github.com/json-schema-org/JSON-Schema-Test-Suite/pull/471
            if (!self::holdsSchemas((string) $propertyName, $parentProperty)) {
                continue;
            }

            if (is_array($member)) {
                foreach ($member as $item) {
                    $this->registerAndScanSubschema($item, $parentId, (string) $propertyName);
                }
                continue;
            }

            $this->registerAndScanSubschema($member, $parentId, (string) $propertyName);
        }
    }

    /**
     * Handle a single member. An object declaring an id is registered under the base uri that
     * id establishes, and everything below it is then scanned against that base uri.
     *
     * @param mixed $subSchema
     */
    private function registerAndScanSubschema($subSchema, string $parentId, string $parentProperty): void
    {
        if (!is_object($subSchema)) {
            $this->scanMembersForSubschemas($subSchema, $parentId, $parentProperty);

            return;
        }

        $childId = $this->registerIdentifiers($subSchema, $parentId);

        $this->scanMembersForSubschemas($subSchema, $childId, $parentProperty);
    }

    /**
     * Register every name a schema object declares and return the base uri for its members
     *
     * An id that is more than a fragment establishes a new base uri, under which the object is
     * registered directly rather than through addSchema(), which would resolve the already
     * resolved id against itself a second time. A plain-name fragment, whether the whole of a
     * draft-04 to draft-07 id or a draft 2019-09 $anchor, names the object without moving the base.
     */
    private function registerIdentifiers(object $schema, string $parentId): string
    {
        $baseId = $this->baseUriOf($schema, $parentId);

        $schemaId = $this->findSchemaIdInObject($schema);
        if (is_string($schemaId) && $schemaId !== '') {
            $resolvedId = $this->uriResolver->resolve($schemaId, $parentId);
            if ($schemaId[0] !== '#') {
                $this->schemas[$baseId] = $schema;
            }
            if ($this->isAnchorReference($resolvedId)) {
                $this->anchors[$resolvedId] = $schema;
            }
        }

        if (property_exists($schema, '$anchor') && is_string($schema->{'$anchor'})) {
            $this->anchors[$this->uriResolver->resolve('#' . $schema->{'$anchor'}, $baseId)] = $schema;
        }

        return $baseId;
    }

    /**
     * The base uri for the members of a schema object: its id resolved against the base uri it
     * sits under, without a fragment, as a fragment names a schema but never forms a base.
     */
    private function baseUriOf(object $schema, string $parentId): string
    {
        $schemaId = $this->findSchemaIdInObject($schema);
        if (!is_string($schemaId) || $schemaId === '' || $schemaId === $parentId) {
            return $parentId;
        }

        return explode('#', $this->uriResolver->resolve($schemaId, $parentId), 2)[0];
    }

    /**
     * Whether the fragment of a uri is a plain name (an anchor) rather than a JSON pointer
     */
    private function isAnchorReference(string $uri): bool
    {
        $fragment = explode('#', $uri, 2)[1] ?? '';

        return $fragment !== '' && $fragment[0] !== '/';
    }

    /**
     * Resolve a reference whose fragment is a plain name (an anchor) rather than a JSON pointer
     *
     * @param list<object> $resolveStack
     *
     * @return mixed
     */
    private function resolveAnchor(string $ref, array $resolveStack)
    {
        // load the document first, as retrieving it is what registers the anchors it declares
        $fileName = explode('#', $ref, 2)[0];
        if ($fileName !== '') {
            $this->getSchema($fileName);
        }

        if (!array_key_exists($ref, $this->anchors)) {
            throw new UnresolvableJsonPointerException(sprintf(
                'File: %s is found, but could not resolve anchor: %s',
                $fileName,
                '#' . explode('#', $ref, 2)[1]
            ));
        }

        return $this->resolveRefSchema($this->anchors[$ref], $resolveStack);
    }

    /**
     * Whether the member named $propertyName holds schemas rather than data.
     *
     * The members of a schema map are named by the schema author, so a member named after a
     * keyword is a schema there, which is what $parentProperty decides.
     */
    private static function holdsSchemas(string $propertyName, string $parentProperty): bool
    {
        if (in_array($parentProperty, self::SCHEMA_MAP_KEYWORDS, true)) {
            return true;
        }

        return in_array($propertyName, self::SCHEMA_KEYWORDS, true)
            || in_array($propertyName, self::SCHEMA_ARRAY_KEYWORDS, true)
            || in_array($propertyName, self::SCHEMA_MAP_KEYWORDS, true);
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
