<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\UriRetrieverInterface;
use JsonSchema\Entity\JsonPointer;

/**
 * The Base Constraints, all Validators should extend this class
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
abstract class Constraint implements ConstraintInterface
{
    protected $errors = array();
    protected $inlineSchemaProperty = '$schema';

    const CHECK_MODE_NORMAL = 		0x00000001;
    const CHECK_MODE_TYPE_CAST = 	0x00000002;
    const CHECK_MODE_COERCE = 		0x00000004;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ? : new Factory();
    }

    /**
     * {@inheritDoc}
     */
    public function addError(JsonPointer $path = null, $message, $constraint='', array $more=null)
    {
        $error = array(
            'property' => $this->convertJsonPointerIntoPropertyPath($path ?: new JsonPointer('')),
            'pointer' => ltrim(strval($path ?: new JsonPointer('')), '#'),
            'message' => $message,
            'constraint' => $constraint,
        );

        if (is_array($more) && count($more) > 0)
        {
            $error += $more;
        }

        $this->errors[] = $error;
    }

    /**
     * {@inheritDoc}
     */
    public function addErrors(array $errors)
    {
        if ($errors) {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid()
    {
        return !$this->getErrors();
    }

    /**
     * Clears any reported errors.  Should be used between
     * multiple validation checks.
     */
    public function reset()
    {
        $this->errors = array();
    }

    /**
     * Bubble down the path
     *
     * @param JsonPointer|null $path Current path
     * @param mixed            $i    What to append to the path
     *
     * @return JsonPointer;
     */
    protected function incrementPath(JsonPointer $path = null, $i)
    {
        $path = $path ?: new JsonPointer('');
        $path = $path->withPropertyPaths(
            array_merge(
                $path->getPropertyPaths(),
                array_filter(array($i), 'strlen')
            )
        );
        return $path;
    }

    /**
     * Validates an array
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkArray(&$value, $schema = null, JsonPointer $path = null, $i = null)
    {
        // Verify minItems
        if (isset($schema->minItems) && count($value) < $schema->minItems) {
            $this->addError($path, "There must be a minimum of " . $schema->minItems . " items in the array", 'minItems', array('minItems' => $schema->minItems,));
        }

        // Verify maxItems
        if (isset($schema->maxItems) && count($value) > $schema->maxItems) {
            $this->addError($path, "There must be a maximum of " . $schema->maxItems . " items in the array", 'maxItems', array('maxItems' => $schema->maxItems,));
        }

        // Verify uniqueItems
        if (isset($schema->uniqueItems) && $schema->uniqueItems) {
            $unique = $value;
            if (is_array($value) && count($value)) {
                $unique = array_map(function($e) { return var_export($e, true); }, $value);
            }
            if (count(array_unique($unique)) != count($value)) {
                $this->addError($path, "There are no duplicates allowed in the array", 'uniqueItems');
            }
        }

        // Verify items
        if (isset($schema->items)) {
            $this->validateItems($value, $schema, $path, $i);
        }
    }

    /**
     * Validates the items
     *
     * @param array            $value
     * @param \stdClass        $schema
     * @param JsonPointer|null $path
     * @param string           $i
     */
    protected function validateItems(&$value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $checkMode = $this->factory->getCheckMode();
        if (is_object($schema->items)) {
            // just one type definition for the whole array
            foreach ($value as $k => $v) {
                if($checkMode & Constraint::CHECK_MODE_TYPE_CAST) {
                    $v = $this->coerce($v, $schema->items);
                    if($checkMode & Constraint::CHECK_MODE_COERCE){
                        $value[$k] = $v;
                    }
                }
                $initErrors = $this->getErrors();

                // First check if its defined in "items"
                $this->checkUndefined($v, $schema->items, $path, $k);

                // Recheck with "additionalItems" if the first test fails
                if (count($initErrors) < count($this->getErrors()) && (isset($schema->additionalItems) && $schema->additionalItems !== false)) {
                    $secondErrors = $this->getErrors();
                    $this->checkUndefined($v, $schema->additionalItems, $path, $k);
                }

                // Reset errors if needed
                if (isset($secondErrors) && count($secondErrors) < count($this->getErrors())) {
                    $this->errors = $secondErrors;
                } elseif (isset($secondErrors) && count($secondErrors) === count($this->getErrors())) {
                    $this->errors = $initErrors;
                }
            }
        } else {
            // Defined item type definitions
            foreach ($value as $k => $v) {
                if (array_key_exists($k, $schema->items)) {
                    if($checkMode & Constraint::CHECK_MODE_TYPE_CAST) {
                        $v = $this->coerce($v, $schema->items[$k]);
                        if($checkMode & Constraint::CHECK_MODE_COERCE){
                            $value[$k] = $v;
                        }
                    }
                    $this->checkUndefined($v, $schema->items[$k], $path, $k);
                } else {
                    // Additional items
                    if (property_exists($schema, 'additionalItems')) {
                        if ($schema->additionalItems !== false) {
                            if($checkMode & Constraint::CHECK_MODE_TYPE_CAST) {
                                $v = $this->coerce($v, $schema->additionalItems);
                                if($checkMode & Constraint::CHECK_MODE_COERCE){
                                    $value[$k] = $v;
                                }
                            }
                            $this->checkUndefined($v, $schema->additionalItems, $path, $k);
                        } else {
                            $this->addError(
                                $path, 'The item ' . $i . '[' . $k . '] is not defined and the definition does not allow additional items', 'additionalItems', array('additionalItems' => $schema->additionalItems,));
                        }
                    } else {
                        // Should be valid against an empty schema
                        $this->checkUndefined($v, new \stdClass(), $path, $k);
                    }
                }
            }

            // Treat when we have more schema definitions than values, not for empty arrays
            if (count($value) > 0) {
                for ($k = count($value); $k < count($schema->items); $k++) {
                    $this->checkUndefined($this->factory->createInstanceFor('undefined'), $schema->items[$k], $path, $k);
                }
            }
        }
    }

    /**
     * Validates an object
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     * @param mixed            $patternProperties
     */
    protected function checkObject($value, $schema = null, JsonPointer $path = null, $i = null, $patternProperties = null)
    {
        $validator = $this->factory->createInstanceFor('object');
        $validator->check($value, $schema, $path, $i, $patternProperties);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Validates the type of a property
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkType($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('type');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a undefined element
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkUndefined($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('undefined');

        $validator->check($value, $this->factory->getSchemaStorage()->resolveRefSchema($schema), $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a string element
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkString($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('string');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a number element
     *
     * @param mixed       $value
     * @param mixed       $schema
     * @param JsonPointer $path
     * @param mixed       $i
     */
    protected function checkNumber($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('number');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a enum element
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkEnum($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('enum');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks format of an element
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkFormat($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('format');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Get the type check based on the set check mode.
     *
     * @return TypeCheck\TypeCheckInterface
     */
    protected function getTypeCheck()
    {
        return $this->factory->getTypeCheck();
    }

    /**
     * @param JsonPointer $pointer
     * @return string property path
     */
    protected function convertJsonPointerIntoPropertyPath(JsonPointer $pointer)
    {
        $result = array_map(
            function($path) {
                return sprintf(is_numeric($path) ? '[%d]' : '.%s', $path);
            },
            $pointer->getPropertyPaths()
        );
        return trim(implode('', $result), '.');
    }

    /**
     * Converts a value to boolean. For example, "true" becomes true.
     * @param $value The value to convert to boolean
     * @return bool|mixed
     */
    protected function toBoolean($value)
    {
        if($value === "true"){
            return true;
        }

        if($value === "false"){
            return false;
        }

        return $value;
    }

    /**
     * Converts a numeric string to a number. For example, "4" becomes 4.
     *
     * @param mixed $value The value to convert to a number.
     * @return int|float|mixed
     */
    protected function toNumber($value)
    {
        if(is_numeric($value)) {
            return $value + 0; // cast to number
        }

        return $value;
    }

    protected function toInteger($value)
    {
        if(is_numeric($value) && (int)$value == $value) {
            return (int)$value; // cast to number
        }

        return $value;
    }

    /**
     * Given a value and a definition, attempts to coerce the value into the
     * type specified by the definition's 'type' property.
     *
     * @param mixed $value Value to coerce.
     * @param \stdClass $definition A definition with information about the expected type.
     * @return bool|int|string
     */
    protected function coerce($value, $definition)
    {
        $types = isset($definition->type)?$definition->type:null;
        if($types){
            foreach((array)$types as $type) {
                switch ($type) {
                    case "boolean":
                        $value = $this->toBoolean($value);
                        break;
                    case "integer":
                        $value = $this->toInteger($value);
                        break;
                    case "number":
                        $value = $this->toNumber($value);
                        break;
                }
            }
        }
        return $value;
    }
}
