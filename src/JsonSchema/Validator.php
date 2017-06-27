<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\InvalidConfigException;
use JsonSchema\SchemaStorage;

/**
 * The main public interface to the JsonSchema validation library.
 *
 * Validates a document instance against a schema
 *
 * @package justinrainbow/json-schema
 *
 * @license MIT
 */
class Validator extends BaseConstraint
{
    const SCHEMA_MEDIA_TYPE = 'application/schema+json';

    const ERROR_NONE                    = 0x00000000;
    const ERROR_ALL                     = 0xFFFFFFFF;
    const ERROR_DOCUMENT_VALIDATION     = 0x00000001;
    const ERROR_SCHEMA_VALIDATION       = 0x00000002;

    /**
     * Validates the given data against the schema and returns an object containing the results.
     *
     * Both the value object and the schema are supposed to be the result of a json_decode call,
     * or an equivalent value. The validation logic used is a non-compliant superset of the spec
     * available at http://json-schema.org/ (versions prior to draft-06).
     *
     * Note that the first argument is passed by reference, so you must pass in a variable.
     *
     * @api
     *
     * @param mixed $value     Reference to the value that should be validated against the schema
     * @param mixed $schema    The schema to validate against
     * @param int   $checkMode Bitwise list of option flags to enable during validation
     *
     * @return int Bitwise list of error categories encountered during validation, or zero for success
     */
    public function validate(&$value, $schema = null, $checkMode = null)
    {
        // reset errors prior to validation
        $this->reset();

        // set checkMode
        $initialCheckMode = $this->factory->getConfig();
        if ($checkMode !== null) {
            $this->factory->setConfig($checkMode);
        }

        // add provided schema to SchemaStorage with internal URI to allow internal $ref resolution
        if (is_object($schema) && property_exists($schema, 'id')) {
            $schemaURI = $schema->id;
        } else {
            $schemaURI = SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        }
        $this->factory->getSchemaStorage()->addSchema($schemaURI, $schema);

        $validator = $this->factory->createInstanceFor('schema');
        $validator->check(
            $value,
            $this->factory->getSchemaStorage()->getSchema($schemaURI)
        );

        $this->factory->setConfig($initialCheckMode);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));

        return $validator->getErrorMask();
    }

    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API.
     *
     * Equivalent to calling validate() without setting any extra $checkMode options
     *
     * @api
     *
     * @param mixed $value  The value that should be validated against the schema - *not* a reference
     * @param mixed $schema The schema to validate against
     *
     * @return int Bitwise list of error categories encountered during validation, or zero for success
     */
    public function check($value, $schema)
    {
        return $this->validate($value, $schema);
    }

    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API.
     *
     * Equivalent to calling validate() with CHECK_MODE_COERCE_TYPES as the only additional
     * $checkMode option.
     *
     * @api
     *
     * @param mixed $value  Reference to the value that should be validated against the schema
     * @param mixed $schema The schema to validate against
     *
     * @return int Bitwise list of error categories encountered during validation, or zero for success
     */
    public function coerce(&$value, $schema)
    {
        return $this->validate($value, $schema, Constraint::CHECK_MODE_COERCE_TYPES);
    }
}
