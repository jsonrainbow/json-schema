<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

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
     * @param mixed  $value
     * @param mixed  $schema
     * @param string $path
     * @param string $i
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
     * @param mixed  $value
     * @param mixed  $schema
     * @param string $path
     * @param string $i
     */
    protected function validateCommonProperties($value, $schema = null, $path = null, $i = null)
    {
        // if it extends another schema, it must pass that schema as well
        if (isset($schema->extends)) {
            if (is_string($schema->extends)) {
                $schema->extends = $this->validateUri($schema, $schema->extends);
            }
            $increment = is_null($i) ? "" : $i;
            if (is_array($schema->extends)) {
                foreach ($schema->extends as $extends) {
                    $this->checkUndefined($value, $extends, $path, $increment);
                }
            } else {
                $this->checkUndefined($value, $schema->extends, $path, $increment);
            }
        }

        // Verify required values
        if (is_object($value)) {
            if ($value instanceof Undefined) {
                // Draft 3 - Required attribute - e.g. "foo": {"type": "string", "required": true}
                if (isset($schema->required) && $schema->required) {
                    $this->addError($path, "is missing and it is required");
                }
            } else if (isset($schema->required)) {
                // Draft 4 - Required is an array of strings - e.g. "required": ["foo", ...]
                foreach ($schema->required as $required) {
                    if (!property_exists($value, $required)) {
                        $this->addError($path, "the property " . $required . " is required");
                    }
                }
            } else {
                $this->checkType($value, $schema, $path);
            }
        } else {
            $this->checkType($value, $schema, $path);
        }

        // Verify disallowed items
        if (isset($schema->disallow)) {
            $initErrors = $this->getErrors();

            $typeSchema = new \stdClass();
            $typeSchema->type = $schema->disallow;
            $this->checkType($value, $typeSchema, $path);

            // if no new errors were raised it must be a disallowed value
            if (count($this->getErrors()) == count($initErrors)) {
                $this->addError($path, " disallowed value was matched");
            } else {
                $this->errors = $initErrors;
            }
        }

        // Verify minimum and maximum number of properties
        if (is_object($value)) {
            if (isset($schema->minProperties)) {
                if (count(get_object_vars($value)) < $schema->minProperties) {
                    $this->addError($path, "must contain a minimum of " + $schema->minProperties + " properties");
                }
            }
            if (isset($schema->maxProperties)) {
                if (count(get_object_vars($value)) > $schema->maxProperties) {
                    $this->addError($path, "must contain no more than " + $schema->maxProperties + " properties");
                }
            }
        }

        // Verify that dependencies are met
        if (is_object($value) && isset($schema->dependencies)) {
            $this->validateDependencies($value, $schema->dependencies, $path);
        }
    }

    /**
     * Validate dependencies
     *
     * @param mixed  $value
     * @param mixed  $dependencies
     * @param string $path
     */
    protected function validateDependencies($value, $dependencies, $path)
    {
        foreach ($dependencies as $key => $dependency) {
            if (property_exists($value, $key)) {
                if (is_string($dependency)) {
                    // Draft 3 string is allowed - e.g. "dependencies": {"bar": "foo"}
                    if (!property_exists($value, $dependency)) {
                        $this->addError($path, "$key depends on $dependency and $dependency is missing");
                    }
                } else if (is_array($dependency)) {
                    // Draft 4 must be an array - e.g. "dependencies": {"bar": ["foo"]}
                    foreach ($dependency as $d) {
                        if (!property_exists($value, $d)) {
                            $this->addError($path, "$key depends on $d and $d is missing");
                        }
                    }
                } else if (is_object($dependency)) {
                    // Schema - e.g. "dependencies": {"bar": {"properties": {"foo": {...}}}}
                    $this->checkUndefined($value, $dependency, $path, "");
                }
            }
        }
    }

    protected function validateUri($schema, $schemaUri = null)
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
