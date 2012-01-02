<?php

namespace JsonSchema\Constraints;

/**
 * The Object Constraints, validates an object against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Object extends Constraint
{
    /**
     * {inheritDoc}
     */
    function check($element, $definition = null, $path = null, $additionalProp = null)
    {
        // validate the definition properties
        $this->validateDefinition($element, $definition, $path);

        // additional the element properties
        $this->validateElement($element, $definition, $path, $additionalProp);
    }

    /**
     * validates the element properties
     *
     * @param \stdClass $element
     * @param \stdClass $objectDefinition
     * @param string $path
     * @param mixed $additionalProp
     */
    public function validateElement($element, $objectDefinition = null, $path = null, $additionalProp = null)
    {
        foreach ($element as $i => $value) {

            $property = $this->getProperty($element, $i, new Undefined());
            $definition = $this->getProperty($objectDefinition, $i);

            //required property
            if ($this->getProperty($definition, 'required') && !$property) {
                $this->addError($path, "the property " . $i . " is required");
            }

            //no additional properties allowed
            if ($additionalProp === false && $this->inlineSchemaProperty !== $i && !$definition) {
                $this->addError($path, "The property " . $i . " is not defined and the definition does not allow additional properties");
            }

            // additional properties defined
            if ($additionalProp && !$definition) {
                $this->checkUndefined($value, $additionalProp, $path, $i);
            }

            // property requires presence of another
            $require = $this->getProperty($definition, 'requires');
            if ($require && !$this->getProperty($element, $require)) {
                $this->addError($path, "the presence of the property " . $i . " requires that " . $require . " also be present");
            }

            //normal property verification
            $this->checkUndefined($value, $definition ? : new \stdClass(), $path, $i);
        }
    }

    /**
     * validates the definition properties
     *
     * @param \stdClass $element
     * @param \stdClass $objectDefinition
     * @param string $path
     */
    public function validateDefinition($element, $objectDefinition = null, $path = null)
    {
        foreach ($objectDefinition as $i => $value) {
            $property = $this->getProperty($element, $i, new Undefined());
            $definition = $this->getProperty($objectDefinition, $i);
            $this->checkUndefined($property, $definition, $path, $i);
        }
    }

    /**
     * retrieves a property from an object or array
     *
     * @param mixed $element
     * @param string $property
     * @param mixed $fallback
     * @return mixed
     */
    protected function getProperty($element, $property, $fallback = null)
    {
        if (is_array($element) /*$this->checkMode == self::CHECK_MODE_TYPE_CAST*/) {
            return array_key_exists($property, $element) ? $element[$property] : $fallback;
        } else {
            return isset($element->$property) ? $element->$property : $fallback;
        }
    }
}