<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Entity\JsonPointer;

/**
 * The ObjectConstraint Constraints, validates an object against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class ObjectConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($element, $definition = null, JsonPointer $path = null, $additionalProp = null, $patternProperties = null)
    {
        if ($element instanceof UndefinedConstraint) {
            return;
        }

        $matches = array();
        if ($patternProperties) {
            $matches = $this->validatePatternProperties($element, $path, $patternProperties);
        }

        if ($definition) {
            // validate the definition properties
            $this->validateDefinition($element, $definition, $path);
        }

        // additional the element properties
        $this->validateElement($element, $matches, $definition, $path, $additionalProp);
    }

    public function validatePatternProperties($element, JsonPointer $path = null, $patternProperties)
    {
        $try = array('/','#','+','~','%');
        $matches = array();
        foreach ($patternProperties as $pregex => $schema) {
            $delimiter = '/';
            // Choose delimiter. Necessary for patterns like ^/ , otherwise you get error
            foreach ($try as $delimiter) {
                if (strpos($pregex, $delimiter) === false) { // safe to use
                    break;
                }
            }

            // Validate the pattern before using it to test for matches
            if (@preg_match($delimiter. $pregex . $delimiter, '') === false) {
                $this->addError($path, 'The pattern "' . $pregex . '" is invalid', 'pregex', array('pregex' => $pregex,));
                continue;
            }
            foreach ($element as $i => $value) {
                if (preg_match($delimiter . $pregex . $delimiter, $i)) {
                    $matches[] = $i;
                    $this->checkUndefined($value, $schema ? : new \stdClass(), $path, $i);
                }
            }
        }
        return $matches;
    }

    /**
     * Validates the element properties
     *
     * @param \stdClass        $element          Element to validate
     * @param array            $matches          Matches from patternProperties (if any)
     * @param \stdClass        $objectDefinition ObjectConstraint definition
     * @param JsonPointer|null $path             Path to test?
     * @param mixed            $additionalProp   Additional properties
     */
    public function validateElement($element, $matches, $objectDefinition = null, JsonPointer $path = null, $additionalProp = null)
    {
        $this->validateMinMaxConstraint($element, $objectDefinition, $path);
        foreach ($element as $i => $value) {
            $definition = $this->getProperty($objectDefinition, $i);

            // no additional properties allowed
            if (!in_array($i, $matches) && $additionalProp === false && $this->inlineSchemaProperty !== $i && !$definition) {
                $this->addError($path, "The property " . $i . " is not defined and the definition does not allow additional properties", 'additionalProp');
            }

            // additional properties defined
            if (!in_array($i, $matches) && $additionalProp && !$definition) {
                if ($additionalProp === true) {
                    $this->checkUndefined($value, null, $path, $i);
                } else {
                    $this->checkUndefined($value, $additionalProp, $path, $i);
                }
            }

            // property requires presence of another
            $require = $this->getProperty($definition, 'requires');
            if ($require && !$this->getProperty($element, $require)) {
                $this->addError($path, "The presence of the property " . $i . " requires that " . $require . " also be present", 'requires');
            }

            $property = $this->getProperty($element, $i, new UndefinedConstraint());
            if (is_object($property)) {
                $this->validateMinMaxConstraint(!($property instanceof UndefinedConstraint) ? $property : $element, $definition, $path);
            }
        }
    }

    /**
     * Validates the definition properties
     *
     * @param \stdClass         $element          Element to validate
     * @param \stdClass         $objectDefinition ObjectConstraint definition
     * @param JsoinPointer|null $path             Path?
     */
    public function validateDefinition($element, $objectDefinition = null, JsonPointer $path = null)
    {
        $default = $this->getFactory()->createInstanceFor('undefined');

        foreach ($objectDefinition as $i => $value) {
            $property = $this->getProperty($element, $i, $default);
            $definition = $this->getProperty($objectDefinition, $i);

            if($this->checkMode & Constraint::CHECK_MODE_TYPE_CAST){
                if(!($property instanceof Constraint)) {
					$property = $this->coerce($property, $definition);

					if($this->checkMode & Constraint::CHECK_MODE_COERCE) {
						if (is_object($element)) {
							$element->{$i} = $property;
						} else {
							$element[$i] = $property;
						}
					}
                }
            }

            if (is_object($definition)) {
                // Undefined constraint will check for is_object() and quit if is not - so why pass it?
                $this->checkUndefined($property, $definition, $path, $i);
            }
        }
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
        if(ctype_digit ($value)) {
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
        $type = isset($definition->type)?$definition->type:null;
        if($type){
            switch($type){
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
        return $value;
    }

    /**
     * retrieves a property from an object or array
     *
     * @param mixed  $element  Element to validate
     * @param string $property Property to retrieve
     * @param mixed  $fallback Default value if property is not found
     *
     * @return mixed
     */
    protected function getProperty($element, $property, $fallback = null)
    {
        if (is_array($element) /*$this->checkMode == self::CHECK_MODE_TYPE_CAST*/) {
            return array_key_exists($property, $element) ? $element[$property] : $fallback;
        } elseif (is_object($element)) {
            return property_exists($element, $property) ? $element->$property : $fallback;
        }

        return $fallback;
    }

    /**
     * validating minimum and maximum property constraints (if present) against an element
     *
     * @param \stdClass        $element          Element to validate
     * @param \stdClass        $objectDefinition ObjectConstraint definition
     * @param JsonPointer|null $path             Path to test?
     */
    protected function validateMinMaxConstraint($element, $objectDefinition, JsonPointer $path = null) {
        // Verify minimum number of properties
        if (isset($objectDefinition->minProperties) && !is_object($objectDefinition->minProperties)) {
            if ($this->getTypeCheck()->propertyCount($element) < $objectDefinition->minProperties) {
                $this->addError($path, "Must contain a minimum of " . $objectDefinition->minProperties . " properties", 'minProperties', array('minProperties' => $objectDefinition->minProperties,));
            }
        }
        // Verify maximum number of properties
        if (isset($objectDefinition->maxProperties) && !is_object($objectDefinition->maxProperties)) {
            if ($this->getTypeCheck()->propertyCount($element) > $objectDefinition->maxProperties) {
                $this->addError($path, "Must contain no more than " . $objectDefinition->maxProperties . " properties", 'maxProperties', array('maxProperties' => $objectDefinition->maxProperties,));
            }
        }
    }
}
