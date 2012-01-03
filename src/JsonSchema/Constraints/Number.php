<?php

namespace JsonSchema\Constraints;

/**
 * The Number Constraints, validates an number against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Number extends Constraint
{
    /**
     * {inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        //verify minimum
        if (isset($schema->minimum) && $element < $schema->minimum) {
            $this->addError($path, "must have a minimum value of " . $schema->minimum);
        }

        //verify maximum
        if (isset($schema->maximum) && $element > $schema->maximum) {
            $this->addError($path, "must have a maximum value of " . $schema->maximum);
        }

        //verify divisibleBy
        if (isset($schema->divisibleBy) && $element % $schema->divisibleBy != 0) {
            $this->addError($path, "is not divisible by " . $schema->divisibleBy);
        }
    }
}