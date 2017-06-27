<?php

namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\UnresolvableJsonPointerException;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;

/**
 * Storage schema for user-provided and retrieved schema objects
 *
 * @package justinrainbow\json-schema
 *
 * @license MIT
 */
class SchemaStorage implements SchemaStorageInterface
{
    /** URI used for schemas which are provided by the user and have no associated URI */
    const INTERNAL_PROVIDED_SCHEMA_URI = 'internal://provided-schema';

    /** @var UriRetriever UriRetriever instance to use for this SchemaStorage */
    protected $uriRetriever;

    /** @var UriResolver UriResolver instance to use for this SchemaStorage */
    protected $uriResolver;

    /** @var array List of cached schemas */
    protected $schemas = array();

    /**
     * Create a new SchemaStorage instance
     *
     * @api
     *
     * @param UriRetriever $uriRetriever UriRetriever instance to use for this SchemaStorage (optional)
     * @param UriResolver  $uriResolver  UriResolver instance to use for this SchemaStorage (optional)
     */
    public function __construct(
        UriRetrieverInterface $uriRetriever = null,
        UriResolverInterface $uriResolver = null
    ) {
        $this->uriRetriever = $uriRetriever ?: new UriRetriever();
        $this->uriResolver = $uriResolver ?: new UriResolver();
    }

    /**
     * Get the UriRetriever instance used for this SchemaStorage
     *
     * @return UriRetrieverInterface
     */
    public function getUriRetriever()
    {
        return $this->uriRetriever;
    }

    /**
     * Get the UriResolver instance used for this SchemaStorage
     *
     * @return UriResolverInterface
     */
    public function getUriResolver()
    {
        return $this->uriResolver;
    }

    /**
     * Add a schema to the cache
     *
     * This method can be used to add a user-provided schema object, or if just a URI is provided,
     * can fetch the schema remotely using the configured UriResolver / UriRetriever objects.
     *
     * @api
     *
     * @param string $id     The unique identifying URI associated with the schema
     * @param mixed  $schema The schema definition (optional)
     */
    public function addSchema($id, $schema = null)
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
            if ($schema->id == 'http://json-schema.org/draft-04/schema#') {
                $schema->properties->id->format = 'uri-reference';
            } elseif ($schema->id == 'http://json-schema.org/draft-03/schema#') {
                $schema->properties->id->format = 'uri-reference';
                $schema->properties->{'$ref'}->format = 'uri-reference';
            }
        }

        // resolve references
        $this->expandRefs($schema, $id);

        $this->schemas[$id] = $schema;
    }

    /**
     * Recursively resolve all references against the provided base
     *
     * @param mixed  $schema
     * @param string $base
     */
    private function expandRefs(&$schema, $base = null)
    {
        if (!is_object($schema)) {
            if (is_array($schema)) {
                foreach ($schema as &$member) {
                    $this->expandRefs($member, $base);
                }
            }

            return;
        }

        if (property_exists($schema, 'id') && is_string($schema->id)) {
            $base = $this->uriResolver->resolve($schema->id, $base);
        }

        if (property_exists($schema, '$ref') && is_string($schema->{'$ref'})) {
            $refPointer = new JsonPointer($this->uriResolver->resolve($schema->{'$ref'}, $base));
            $schema->{'$ref'} = (string) $refPointer;
        }

        foreach ($schema as &$member) {
            $this->expandRefs($member, $base);
        }
    }

    /**
     * Get a decoded schema
     *
     * If the schema is present in the cache, it will be returned directly. Otherwise, the library
     * will fetch it remotely using the configured UriResolver / UriRetriever objects.
     *
     * @api
     *
     * @param string $id The unique identifying URI associated with the schema
     *
     * @return mixed The decoded schema definition
     */
    public function getSchema($id)
    {
        if (!array_key_exists($id, $this->schemas)) {
            $this->addSchema($id);
        }

        return $this->schemas[$id];
    }

    /**
     * Resolve a uri-reference pointer and return the target schema object
     *
     * @param string $ref          The reference to resolve
     * @param array  $resolveStack Internal list of resolved objects (used for loop detection)
     *
     * @return mixed The referenced schema object
     */
    public function resolveRef($ref, $resolveStack = array())
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
     * Check a schema object for $ref, and resolve it if present. Returns the processed schema object.
     *
     * @param mixed $refSchema    The schema to check
     * @param array $resolveStack Internal list of resolved objects (used for loop detection)
     *
     * @return mixed The final schema object after all $ref properties have been recursively resolved
     */
    public function resolveRefSchema($refSchema, $resolveStack = array())
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

        return $refSchema;
    }
}
