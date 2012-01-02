<?php

namespace JsonSchema\Constraints;

/**
 * The Enum Constraints, validates an element against a given set of possibilities
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Enum extends Constraint
{
    /**
     * {inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        foreach ($schema->enum as $possibleValue) {
            if ($possibleValue == $element) {
                $found = true;
                break;
            }
        }

        if (!isset($found)) {
            $this->addError($path, "does not have a value in the enumeration " . implode(', ', $schema->enum));
        }
    }
}