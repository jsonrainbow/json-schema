<?php

namespace JsonSchema\Constraints;

/**
 * The Collection Constraints, validates an array against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Collection extends Constraint
{
    /**
     * {inheritDoc}
     */
    public function check($value, $schema = null, $path = null, $i = null)
    {
        // verify minItems
        if (isset($schema->minItems) && count($value) < $schema->minItems) {
            $this->addError($path, "There must be a minimum of " . $schema->minItems . " in the array");
        }
        // verify maxItems
        if (isset($schema->maxItems) && count($value) > $schema->maxItems) {
            $this->addError($path, "There must be a maximum of " . $schema->maxItems . " in the array");
        }
        // verify uniqueItems
        //TODO array_unique doesnt work with objects
        if (isset($schema->uniqueItems) && array_unique($value) != $value) {
            $this->addError($path, "There are no duplicates allowed in the array");
        }

        //verify items
        if (isset($schema->items)) {
            $this->validateItems($value, $schema, $path, $i);
        }
    }

    /**
     * validates the items
     *
     * @param array $value
     * @param \stdClass $schema
     * @param string $path
     * @param string $i
     */
    protected function validateItems($value, $schema = null, $path = null, $i = null)
    {
        if (!is_array($schema->items)) {
            // just one type definition for the whole array
            foreach ($value as $k => $v) {
                $initErrors = $this->getErrors();

                //first check if its defined in "items"
                if (!isset($schema->additionalItems) || $schema->additionalItems === false) {
                    $this->checkUndefined($v, $schema->items, $path, $k);
                }

                //recheck with "additionalItems" if the first test fails
                if (count($initErrors) < count($this->getErrors()) && (isset($schema->additionalItems) && $schema->additionalItems !== false)) {
                    $secondErrors = $this->getErrors();
                    $this->checkUndefined($v, $schema->additionalItems, $path, $k);
                }

                //reset errors if needed
                if (isset($secondErrors) && count($secondErrors) < $this->getErrors()) {
                    $this->errors = $secondErrors;
                } elseif (isset($secondErrors) && count($secondErrors) == count($this->getErrors())) {
                    $this->errors = $initErrors;
                }
            }
        } else {
            //defined item type definitions
            foreach ($value as $k => $v) {
                if (array_key_exists($k, $schema->items)) {
                    $this->checkUndefined($v, $schema->items[$k], $path, $k);
                } else {
                    // additional items
                    if (array_key_exists('additionalItems', $schema) && $schema->additionalItems !== false) {
                        $this->checkUndefined($v, $schema->additionalItems, $path, $k);
                    } else {
                        $this->addError(
                            $path,
                            'The item ' . $i . '[' . $k . '] is not defined in the objTypeDef and the objTypeDef does not allow additional properties'
                        );
                    }
                }
            }

            // treat when we have more schema definitions than values
            for ($k = count($value); $k < count($schema->items); $k++) {
                $this->checkUndefined(new Undefined(), $schema->items[$k], $path, $k);
            }
        }
    }
}