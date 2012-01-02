<?php

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
     * {inheritDoc}
     */
    function check($value, $schema = null, $path = null, $i = null)
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
     * validates the value against the types
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
        if (is_object($value) && isset($schema->properties)) {
            $this->checkObject($value, $schema->properties, $path, isset($schema->additionalProperties) ? $schema->additionalProperties : null);
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
     * validates common properties
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
            $this->checkUndefined($value, $schema->extends, $path, $i);
        }

        // verify required values
        if (is_object($value) && $value instanceOf Undefined) {
            if (isset($schema->required) && $schema->required) {
                $this->addError($path, "is missing and it is required");
            }
        } else {
            $this->checkType($value, $schema, $path);
        }

        //verify disallowed items
        if (isset($schema->disallow)) {
            $initErrors = $this->getErrors();

            $this->checkUndefined($value, $schema->disallow, $path);

            //if no new errors were raised it must be a disallowed value
            if (count($this->getErrors()) == count($initErrors)) {
                $this->addError($path, " disallowed value was matched");
            } else {
                $this->errors = $initErrors;
            }
        }
    }
}