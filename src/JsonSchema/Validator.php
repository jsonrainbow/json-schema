<?php

declare(strict_types=1);

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\TypeCheck\LooseTypeCheck;
use JsonSchema\Exception\InvalidArgumentException;

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
    public const SCHEMA_MEDIA_TYPE = 'application/schema+json';

    public const ERROR_NONE                    = 0;
    public const ERROR_ALL                     = -1;
    public const ERROR_DOCUMENT_VALIDATION     = 1;
    public const ERROR_SCHEMA_VALIDATION       = 2;

    /**
     * Validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org.
     *
     * Note that the first argument is passed by reference, so you must pass in a variable.
     *
     * @param mixed $value
     * @param mixed $schema
     *
     * @throws InvalidArgumentException if the schema is neither an object, an array, a boolean nor null
     *
     * @phpstan-param int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     * @phpstan-return int-mask-of<Validator::ERROR_*>
     */
    public function validate(&$value, $schema = null, ?int $checkMode = null): int
    {
        // A JSON string or any other scalar is not a schema, it would pass every value silently
        if ($schema !== null && !is_object($schema) && !is_array($schema) && !is_bool($schema)) {
            throw new InvalidArgumentException(sprintf('The schema must be an object, an array or a boolean, %s given', gettype($schema)));
        }

        // cast array schemas to object
        if (is_array($schema)) {
            $schema = BaseConstraint::arrayToObjectRecursive($schema);
        }

        // reset errors prior to validation
        $this->reset();

        // set checkMode
        $initialCheckMode = $this->factory->getConfig();
        if ($checkMode !== null) {
            $this->factory->setConfig($checkMode);
        }

        // add provided schema to SchemaStorage with internal URI to allow internal $ref resolution
        $schemaURI = SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        if (LooseTypeCheck::propertyExists($schema, 'id')) {
            $schemaURI = LooseTypeCheck::propertyGet($schema, 'id');
        }
        if (LooseTypeCheck::propertyExists($schema, '$id')) {
            $schemaURI = LooseTypeCheck::propertyGet($schema, '$id');
        }
        $this->factory->getSchemaStorage()->addSchema($schemaURI, $schema);

        $validator = $this->factory->createInstanceFor('schema');
        $schema = $this->factory->getSchemaStorage()->getSchema($schemaURI);

        // Boolean schema requires no further validation
        if (is_bool($schema)) {
            if ($schema === false) {
                $this->addError(ConstraintError::FALSE());
            }

            return $this->getErrorMask();
        }

        if ($this->factory->getConfig(Constraint::CHECK_MODE_STRICT)) {
            // The strict mode has no inline schema to fall back to, unlike SchemaConstraint
            if (!is_object($schema)) {
                throw new InvalidArgumentException('no schema found to verify against');
            }

            $dialect = $this->factory->getDefaultDialect();
            if (property_exists($schema, '$schema')) {
                $dialect = $schema->{'$schema'};
            }

            $validator = $this->factory->createInstanceFor(
                DraftIdentifiers::byValue($dialect)->toConstraintName()
            );
        }

        $validator->check($value, $schema);

        $this->factory->setConfig($initialCheckMode);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));

        return $validator->getErrorMask();
    }

    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API
     *
     * @deprecated since 6.0.0, use Validator::validate() instead, to be removed in 7.0
     *
     * @param mixed $value
     * @param mixed $schema
     *
     * @phpstan-return int-mask-of<Validator::ERROR_*>
     */
    public function check($value, $schema): int
    {
        return $this->validate($value, $schema);
    }

    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API
     *
     * @deprecated since 6.0.0, use Validator::validate() instead, to be removed in 7.0
     *
     * @param mixed $value
     * @param mixed $schema
     *
     * @phpstan-return int-mask-of<Validator::ERROR_*>
     */
    public function coerce(&$value, $schema): int
    {
        return $this->validate($value, $schema, Constraint::CHECK_MODE_COERCE_TYPES);
    }
}
