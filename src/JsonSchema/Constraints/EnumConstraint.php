<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

/**
 * The EnumConstraint Constraints, validates an element against a given set of possibilities
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class EnumConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        // Only validate enum if the attribute exists
        if ($element instanceof UndefinedConstraint && (!isset($schema->required) || !$schema->required)) {
            return;
        }

        foreach ($schema->enum as $enum) {
            $type = gettype($element);
            if ($type === gettype($enum)) {
                if ($type == "object") {
                    if ($element == $enum)
                        return;
                } else {
                    if ($element === $enum)
                        return;

                }
            }
        }

        $this->addError($path, "value is not in enumeration: [" . $this->enumToString($schema->enum) . "]", 'enum', array('enum' => $schema->enum,));
    }

    /**
     * @param array $enum
     * @return string
     */
    private function enumToString(array $enum)
    {
        $enumString = '';

        foreach ($enum as $value) {
            if (is_array($value)) {
                $enumString += '[' . $this->enumToString($value) . ']' . ', ';
                continue;
            }

            if (is_object($value)) {
                $enumString += '[' . $this->enumToString((array) $value) . ']' . ', ';
                continue;
            }

            $enumString += $value . ', ';
        }

        return rtrim($enumString, ', ');
    }
}
