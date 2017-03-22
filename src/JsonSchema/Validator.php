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
 * A JsonSchema Constraint
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 *
 * @see    README.md
 */
class Validator extends BaseConstraint
{
    const SCHEMA_MEDIA_TYPE = 'application/schema+json';

    const ERROR_NONE                    = 0x00000000;
    const ERROR_ALL                     = 0xFFFFFFFF;
    const ERROR_DOCUMENT_VALIDATION     = 0x00000001;
    const ERROR_SCHEMA_VALIDATION       = 0x00000002;

    /**
     * Validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org.
     *
     * Note that the first argument is passwd by reference, so you must pass in a variable.
     *
     * {@inheritdoc}
     */
    public function validate(&$value, $schema = null, $checkMode = null)
    {
        // make sure $schema is an object
        if (is_array($schema)) {
            $schema = self::arrayToObjectRecursive($schema);
        }

        // set checkMode
        $initialCheckMode = $this->factory->getConfig();
        if ($checkMode !== null) {
            $this->factory->setConfig($checkMode);
        }

        // add provided schema to SchemaStorage with internal URI to allow internal $ref resolution
        $this->factory->getSchemaStorage()->addSchema(SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI, $schema);

        $validator = $this->factory->createInstanceFor('schema');
        $validator->check($value, $schema);

        $this->factory->setConfig($initialCheckMode);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));

        return $validator->getErrorMask();
    }

    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API
     */
    public function check($value, $schema)
    {
        return $this->validate($value, $schema);
    }

    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API
     */
    public function coerce(&$value, $schema)
    {
        return $this->validate($value, $schema, Constraint::CHECK_MODE_COERCE_TYPES);
    }
}
