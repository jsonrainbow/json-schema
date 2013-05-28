<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        // Only validate enum if the attribute exists
        if ($element instanceof Undefined && (!isset($schema->required) || !$schema->required))  {
            return;
        }

        foreach ($schema->enum as $enum) {
            if ((gettype($element) === gettype($enum)) && ($element == $enum)) {
                return;
            }
        }

        $this->addError($path, "does not have a value in the enumeration " . print_r($schema->enum, true));
    }
}