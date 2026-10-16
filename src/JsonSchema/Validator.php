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
use JsonSchema\Constraints\Factory;
use JsonSchema\Constraints\TypeCheck\LooseTypeCheck;
use JsonSchema\Exception\InvalidSchemaException;

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
     * @phpstan-param int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     * @phpstan-return int-mask-of<Validator::ERROR_*>
     */
    public function validate(&$value, $schema = null, ?int $checkMode = null): int
    {
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
            $dialect = $this->factory->getDefaultDialect();
            if (property_exists($schema, '$schema')) {
                $dialect = $schema->{'$schema'};
            }

            $constraintName = DraftIdentifiers::byValue($dialect)->toConstraintName();
            $validator = $this->factory->createInstanceFor($constraintName);

            if ($this->factory->getConfig(Constraint::CHECK_MODE_VALIDATE_SCHEMA)) {
                $this->validateSchema($schema, $dialect, $constraintName);
            }
        }

        $validator->check($value, $schema);

        $this->factory->setConfig($initialCheckMode);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));

        return $this->getErrorMask();
    }

    /**
     * Validates the schema against the meta-schema of its dialect, using the constraints of that dialect
     */
    private function validateSchema(object $schema, string $dialect, string $constraintName): void
    {
        $schemaStorage = $this->factory->getSchemaStorage();
        $metaSchema = $schemaStorage->getSchema($dialect);

        // the meta-schema must neither be validated itself nor modify the schema
        $factory = new Factory($schemaStorage, $this->factory->getUriRetriever(), $this->factory->getConfig());
        $factory->removeConfig(Constraint::CHECK_MODE_VALIDATE_SCHEMA | Constraint::CHECK_MODE_APPLY_DEFAULTS);
        $factory->addConfig(Constraint::CHECK_MODE_TYPE_CAST);
        $validator = $factory->createInstanceFor($constraintName);

        try {
            $validator->check($schema, $metaSchema);
        } catch (\Exception $e) {
            if ($this->factory->getConfig(Constraint::CHECK_MODE_EXCEPTIONS)) {
                throw new InvalidSchemaException('Schema did not pass validation', 0, $e);
            }
        }

        $errors = $validator->getErrors();
        if (count($errors) === 0) {
            return;
        }

        foreach ($errors as &$error) {
            $error['context'] = self::ERROR_SCHEMA_VALIDATION;
        }
        unset($error);

        $this->addErrors($errors);

        $initialContext = $this->factory->getErrorContext();
        $this->factory->setErrorContext(self::ERROR_SCHEMA_VALIDATION);
        $this->addError(ConstraintError::INVALID_SCHEMA());
        $this->factory->setErrorContext($initialContext);
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
