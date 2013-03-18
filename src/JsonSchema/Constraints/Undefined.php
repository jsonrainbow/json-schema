<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Validator;

/**
 * The Undefined Constraints
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Undefined extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($value, $schema = null, $path = null, $i = null)
    {
        if (!is_object($schema)) {
            return;
        }

        $path = $this->incrementPath($path, $i);

        // check special properties
        $this->validateCommonProperties($value, $schema, $path);

        // check known types
        $this->validateTypes($value, $schema, $path, $i);
    }

    /**
     * Validates the value against the types
     *
     * @param $value
     * @param null $schema
     * @param null $path
     * @param null $i
     */
    public function validateTypes($value, $schema = null, $path = null, $i = null)
    {
        // check array
        if (is_array($value)) {
            $this->checkArray($value, $schema, $path, $i);
        }

        // check object
        if (is_object($value) && (isset($schema->properties) || isset($schema->patternProperties))) {
            $this->checkObject(
                $value,
                isset($schema->properties) ? $schema->properties : null,
                $path,
                isset($schema->additionalProperties) ? $schema->additionalProperties : null,
                isset($schema->patternProperties) ? $schema->patternProperties : null
            );
        }

        // check string
        if (is_string($value)) {
            $this->checkString($value, $schema, $path, $i);
        }

        // check numeric
        if (is_numeric($value)) {
            $this->checkNumber($value, $schema, $path, $i);
        }

        // check enum
        if (isset($schema->enum)) {
            $this->checkEnum($value, $schema, $path, $i);
        }
    }

    /**
     * Validates common properties
     *
     * @param $value
     * @param null $schema
     * @param null $path
     * @param null $i
     */
    protected function validateCommonProperties($value, $schema = null, $path = null, $i = null)
    {
        // if it extends another schema, it must pass that schema as well
        if (isset($schema->extends)) {
            if (is_string($schema->extends)) {
                $schema->extends = $this->validateUri($schema->extends, $schema, $path, $i);
            }
            $this->checkUndefined($value, $schema->extends, $path, $i);
        }

        // Verify required values
        if (is_object($value) && $value instanceof Undefined) {
            if (isset($schema->required) && $schema->required) {
                $this->addError($path, "is missing and it is required");
            }
        } else {
            $this->checkType($value, $schema, $path);
        }

        // Verify disallowed items
        if (isset($schema->disallow)) {
            $initErrors = $this->getErrors();

            $this->checkUndefined($value, $schema->disallow, $path);

            // if no new errors were raised it must be a disallowed value
            if (count($this->getErrors()) == count($initErrors)) {
                $this->addError($path, " disallowed value was matched");
            } else {
                $this->errors = $initErrors;
            }
        }
    }

    protected function validateUri($schemaUri = null, $schema, $path = null, $i = null)
    {
        $resolver = new \JsonSchema\Uri\UriResolver();
        $retriever = $this->getUriRetriever();

        if ($resolver->isValid($schemaUri)) {
            $schemaId = property_exists($schema, 'id') ? $schema->id : null;
            $jsonSchema = $retriever->retrieve($schemaId, $schemaUri);

            return $jsonSchema;
        }
    }
}
