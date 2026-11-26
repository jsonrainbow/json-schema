<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\InvalidSchemaException;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

class Draft2019Constraint extends Constraint
{
    public function __construct(?\JsonSchema\Constraints\Factory $factory = null)
    {
        parent::__construct(new Factory(
            $factory ? $factory->getSchemaStorage() : new SchemaStorage(),
            $factory ? $factory->getUriRetriever() : new UriRetriever(),
            $factory ? $factory->getConfig() : Constraint::CHECK_MODE_NORMAL
        ));
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (is_bool($schema)) {
            if ($schema === false) {
                $this->addError(ConstraintError::FALSE(), $path, []);
            }

            return;
        }

        if ($this->factory->getConfig(Constraint::CHECK_MODE_VALIDATE_SCHEMA)) {
            // validate the schema only once, not each of its subschemas
            $this->factory->removeConfig(Constraint::CHECK_MODE_VALIDATE_SCHEMA);
            try {
                $this->validateSchema($schema, $path);
                $this->check($value, $schema, $path, $i);
            } finally {
                $this->factory->addConfig(Constraint::CHECK_MODE_VALIDATE_SCHEMA);
            }

            return;
        }

        // Apply defaults
        $this->checkForKeyword('ref', $value, $schema, $path, $i);
        $this->checkForKeyword('required', $value, $schema, $path, $i);
        $this->checkForKeyword('contains', $value, $schema, $path, $i);
        $this->checkForKeyword('properties', $value, $schema, $path, $i);
        $this->checkForKeyword('propertyNames', $value, $schema, $path, $i);
        $this->checkForKeyword('patternProperties', $value, $schema, $path, $i);
        $this->checkForKeyword('type', $value, $schema, $path, $i);
        $this->checkForKeyword('not', $value, $schema, $path, $i);
        $this->checkForKeyword('dependentSchemas', $value, $schema, $path, $i);
        $this->checkForKeyword('dependentRequired', $value, $schema, $path, $i);
        $this->checkForKeyword('allOf', $value, $schema, $path, $i);
        $this->checkForKeyword('anyOf', $value, $schema, $path, $i);
        $this->checkForKeyword('oneOf', $value, $schema, $path, $i);
        $this->checkForKeyword('ifThenElse', $value, $schema, $path, $i);
        $this->checkForKeyword('unevaluatedProperties', $value, $schema, $path, $i);

        $this->checkForKeyword('additionalProperties', $value, $schema, $path, $i);
        $this->checkForKeyword('items', $value, $schema, $path, $i);
        $this->checkForKeyword('additionalItems', $value, $schema, $path, $i);
        $this->checkForKeyword('uniqueItems', $value, $schema, $path, $i);
        $this->checkForKeyword('minItems', $value, $schema, $path, $i);
        $this->checkForKeyword('minProperties', $value, $schema, $path, $i);
        $this->checkForKeyword('maxProperties', $value, $schema, $path, $i);
        $this->checkForKeyword('minimum', $value, $schema, $path, $i);
        $this->checkForKeyword('maximum', $value, $schema, $path, $i);
        $this->checkForKeyword('minLength', $value, $schema, $path, $i);
        $this->checkForKeyword('exclusiveMinimum', $value, $schema, $path, $i);
        $this->checkForKeyword('maxItems', $value, $schema, $path, $i);
        $this->checkForKeyword('maxLength', $value, $schema, $path, $i);
        $this->checkForKeyword('exclusiveMaximum', $value, $schema, $path, $i);
        $this->checkForKeyword('enum', $value, $schema, $path, $i);
        $this->checkForKeyword('const', $value, $schema, $path, $i);
        $this->checkForKeyword('multipleOf', $value, $schema, $path, $i);
        $this->checkForKeyword('format', $value, $schema, $path, $i);
        $this->checkForKeyword('pattern', $value, $schema, $path, $i);
        $this->checkForKeyword('content', $value, $schema, $path, $i);
    }

    private const KEYWORD_SCHEMA_PROPERTIES = [
        'ref' => ['$ref'],
        'ifThenElse' => ['if'],
        'content' => ['contentMediaType', 'contentEncoding'],
    ];

    /**
     * Validates the schema against the meta-schema of this draft
     *
     * @param mixed $schema
     */
    private function validateSchema($schema, ?JsonPointer $path): void
    {
        $initialConfig = $this->factory->getConfig();
        // the meta-schema must not modify the schema
        $this->factory->removeConfig(Constraint::CHECK_MODE_APPLY_DEFAULTS);
        $this->factory->addConfig(Constraint::CHECK_MODE_TYPE_CAST);
        $validator = new self($this->factory);
        $this->factory->setConfig($initialConfig);

        $metaSchema = $this->factory->getSchemaStorage()->getSchema(DraftIdentifiers::DRAFT_2019_09);
        try {
            $validator->check($schema, $metaSchema);
        } catch (\Exception $e) {
            if ($this->factory->getConfig(Constraint::CHECK_MODE_EXCEPTIONS)) {
                throw new InvalidSchemaException('Schema did not pass validation', 0, $e);
            }
        }

        $errors = $validator->getErrors();
        if ($errors === []) {
            return;
        }

        foreach ($errors as &$error) {
            $error['context'] = Validator::ERROR_SCHEMA_VALIDATION;
        }
        unset($error);
        $this->addErrors($errors);

        $initialContext = $this->factory->getErrorContext();
        $this->factory->setErrorContext(Validator::ERROR_SCHEMA_VALIDATION);
        $this->addError(ConstraintError::INVALID_SCHEMA(), $path);
        $this->factory->setErrorContext($initialContext);
    }

    /**
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $i
     */
    protected function checkForKeyword(string $keyword, $value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        foreach (self::KEYWORD_SCHEMA_PROPERTIES[$keyword] ?? [$keyword] as $property) {
            if (property_exists($schema, $property)) {
                $validator = $this->factory->createInstanceFor($keyword);
                $validator->check($value, $schema, $path, $i);

                $this->addErrors($validator->getErrors());

                return;
            }
        }
    }
}
