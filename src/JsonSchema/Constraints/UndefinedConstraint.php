<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Constraints\TypeCheck\LooseTypeCheck;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Uri\UriResolver;

/**
 * The UndefinedConstraint Constraints
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class UndefinedConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function check(&$value, $schema = null, JsonPointer $path = null, $i = null)
    {
        if (is_null($schema) || !is_object($schema)) {
            return;
        }

        $path = $this->incrementPath($path ?: new JsonPointer(''), $i);

        // check special properties
        $this->validateCommonProperties($value, $schema, $path, $i);

        // check allOf, anyOf, and oneOf properties
        $this->validateOfProperties($value, $schema, $path, '');

        // check known types
        $this->validateTypes($value, $schema, $path, $i);
    }

    /**
     * Validates the value against the types
     *
     * @param mixed       $value
     * @param mixed       $schema
     * @param JsonPointer $path
     * @param string      $i
     */
    public function validateTypes(&$value, $schema = null, JsonPointer $path, $i = null)
    {
        // check array
        if ($this->getTypeCheck()->isArray($value)) {
            $this->checkArray($value, $schema, $path, $i);
        }

        // check object
        if (LooseTypeCheck::isObject($value)) { // object processing should always be run on assoc arrays,
                                                // so use LooseTypeCheck here even if CHECK_MODE_TYPE_CAST
                                                // is not set (i.e. don't use $this->getTypeCheck() here).
            $this->checkObject(
                $value,
                isset($schema->properties) ? $this->factory->getSchemaStorage()->resolveRefSchema($schema->properties) : $schema,
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
     * @param mixed       $value
     * @param mixed       $schema
     * @param JsonPointer $path
     * @param string      $i
     */
    protected function validateCommonProperties(&$value, $schema = null, JsonPointer $path, $i = '')
    {
        // if it extends another schema, it must pass that schema as well
        if (isset($schema->extends)) {
            if (is_string($schema->extends)) {
                $schema->extends = $this->validateUri($schema, $schema->extends);
            }
            if (is_array($schema->extends)) {
                foreach ($schema->extends as $extends) {
                    $this->checkUndefined($value, $extends, $path, $i);
                }
            } else {
                $this->checkUndefined($value, $schema->extends, $path, $i);
            }
        }

        // Apply default values from schema
        if ($this->factory->getConfig(self::CHECK_MODE_APPLY_DEFAULTS)) {
            if ($this->getTypeCheck()->isObject($value) && isset($schema->properties)) {
                // $value is an object, so apply default properties if defined
                foreach ($schema->properties as $i => $propertyDefinition) {
                    if (!$this->getTypeCheck()->propertyExists($value, $i) && isset($propertyDefinition->default)) {
                        if (is_object($propertyDefinition->default)) {
                            $this->getTypeCheck()->propertySet($value, $i, clone $propertyDefinition->default);
                        } else {
                            $this->getTypeCheck()->propertySet($value, $i, $propertyDefinition->default);
                        }
                    }
                }
            } elseif ($this->getTypeCheck()->isArray($value)) {
                if (isset($schema->properties)) {
                    // $value is an array, but default properties are defined, so treat as assoc
                    foreach ($schema->properties as $i => $propertyDefinition) {
                        if (!isset($value[$i]) && isset($propertyDefinition->default)) {
                            if (is_object($propertyDefinition->default)) {
                                $value[$i] = clone $propertyDefinition->default;
                            } else {
                                $value[$i] = $propertyDefinition->default;
                            }
                        }
                    }
                } elseif (isset($schema->items)) {
                    // $value is an array, and default items are defined - treat as plain array
                    foreach ($schema->items as $i => $itemDefinition) {
                        if (!isset($value[$i]) && isset($itemDefinition->default)) {
                            if (is_object($itemDefinition->default)) {
                                $value[$i] = clone $itemDefinition->default;
                            } else {
                                $value[$i] = $itemDefinition->default;
                            }
                        }
                    }
                }
            } elseif (($value instanceof self || $value === null) && isset($schema->default)) {
                // $value is a leaf, not a container - apply the default directly
                $value = is_object($schema->default) ? clone $schema->default : $schema->default;
            }
        }

        // Verify required values
        if ($this->getTypeCheck()->isObject($value)) {
            if (!($value instanceof self) && isset($schema->required) && is_array($schema->required)) {
                // Draft 4 - Required is an array of strings - e.g. "required": ["foo", ...]
                foreach ($schema->required as $required) {
                    if (!$this->getTypeCheck()->propertyExists($value, $required)) {
                        $this->addError(
                            $this->incrementPath($path ?: new JsonPointer(''), $required),
                            'The property ' . $required . ' is required',
                            'required'
                        );
                    }
                }
            } elseif (isset($schema->required) && !is_array($schema->required)) {
                // Draft 3 - Required attribute - e.g. "foo": {"type": "string", "required": true}
                if ($schema->required && $value instanceof self) {
                    $this->addError($path, 'Is missing and it is required', 'required');
                }
            }
        }

        // Verify type
        if (!($value instanceof self)) {
            $this->checkType($value, $schema, $path, $i);
        }

        // Verify disallowed items
        if (isset($schema->disallow)) {
            $initErrors = $this->getErrors();

            $typeSchema = new \stdClass();
            $typeSchema->type = $schema->disallow;
            $this->checkType($value, $typeSchema, $path);

            // if no new errors were raised it must be a disallowed value
            if (count($this->getErrors()) == count($initErrors)) {
                $this->addError($path, 'Disallowed value was matched', 'disallow');
            } else {
                $this->errors = $initErrors;
            }
        }

        if (isset($schema->not)) {
            $initErrors = $this->getErrors();
            $this->checkUndefined($value, $schema->not, $path, $i);

            // if no new errors were raised then the instance validated against the "not" schema
            if (count($this->getErrors()) == count($initErrors)) {
                $this->addError($path, 'Matched a schema which it should not', 'not');
            } else {
                $this->errors = $initErrors;
            }
        }

        // Verify that dependencies are met
        if (isset($schema->dependencies) && $this->getTypeCheck()->isObject($value)) {
            $this->validateDependencies($value, $schema->dependencies, $path);
        }
    }

    /**
     * Validate allOf, anyOf, and oneOf properties
     *
     * @param mixed       $value
     * @param mixed       $schema
     * @param JsonPointer $path
     * @param string      $i
     */
    protected function validateOfProperties(&$value, $schema, JsonPointer $path, $i = '')
    {
        // Verify type
        if ($value instanceof self) {
            return;
        }

        if (isset($schema->allOf)) {
            $isValid = true;
            foreach ($schema->allOf as $allOf) {
                $initErrors = $this->getErrors();
                $this->checkUndefined($value, $allOf, $path, $i);
                $isValid = $isValid && (count($this->getErrors()) == count($initErrors));
            }
            if (!$isValid) {
                $this->addError($path, 'Failed to match all schemas', 'allOf');
            }
        }

        if (isset($schema->anyOf)) {
            $isValid = false;
            $startErrors = $this->getErrors();
            foreach ($schema->anyOf as $anyOf) {
                $initErrors = $this->getErrors();
                $this->checkUndefined($value, $anyOf, $path, $i);
                if ($isValid = (count($this->getErrors()) == count($initErrors))) {
                    break;
                }
            }
            if (!$isValid) {
                $this->addError($path, 'Failed to match at least one schema', 'anyOf');
            } else {
                $this->errors = $startErrors;
            }
        }

        if (isset($schema->oneOf)) {
            $allErrors = array();
            $matchedSchemas = 0;
            $startErrors = $this->getErrors();
            foreach ($schema->oneOf as $oneOf) {
                $this->errors = array();
                $this->checkUndefined($value, $oneOf, $path, $i);
                if (count($this->getErrors()) == 0) {
                    $matchedSchemas++;
                }
                $allErrors = array_merge($allErrors, array_values($this->getErrors()));
            }
            if ($matchedSchemas !== 1) {
                $this->addErrors(array_merge($allErrors, $startErrors));
                $this->addError($path, 'Failed to match exactly one schema', 'oneOf');
            } else {
                $this->errors = $startErrors;
            }
        }
    }

    /**
     * Validate dependencies
     *
     * @param mixed       $value
     * @param mixed       $dependencies
     * @param JsonPointer $path
     * @param string      $i
     */
    protected function validateDependencies($value, $dependencies, JsonPointer $path, $i = '')
    {
        foreach ($dependencies as $key => $dependency) {
            if ($this->getTypeCheck()->propertyExists($value, $key)) {
                if (is_string($dependency)) {
                    // Draft 3 string is allowed - e.g. "dependencies": {"bar": "foo"}
                    if (!$this->getTypeCheck()->propertyExists($value, $dependency)) {
                        $this->addError($path, "$key depends on $dependency and $dependency is missing", 'dependencies');
                    }
                } elseif (is_array($dependency)) {
                    // Draft 4 must be an array - e.g. "dependencies": {"bar": ["foo"]}
                    foreach ($dependency as $d) {
                        if (!$this->getTypeCheck()->propertyExists($value, $d)) {
                            $this->addError($path, "$key depends on $d and $d is missing", 'dependencies');
                        }
                    }
                } elseif (is_object($dependency)) {
                    // Schema - e.g. "dependencies": {"bar": {"properties": {"foo": {...}}}}
                    $this->checkUndefined($value, $dependency, $path, $i);
                }
            }
        }
    }

    protected function validateUri($schema, $schemaUri = null)
    {
        $resolver = new UriResolver();
        $retriever = $this->factory->getUriRetriever();

        $jsonSchema = null;
        if ($resolver->isValid($schemaUri)) {
            $schemaId = property_exists($schema, 'id') ? $schema->id : null;
            $jsonSchema = $retriever->retrieve($schemaId, $schemaUri);
        }

        return $jsonSchema;
    }
}
