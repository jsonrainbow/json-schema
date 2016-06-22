<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use robotdance\I18n;

/**
 * The NumberConstraint Constraints, validates an number against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class NumberConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        // Verify minimum
        if (isset($schema->exclusiveMinimum)) {
            if (isset($schema->minimum)) {
                $errorMsg = I18n::t("constraints.number.minimum", ['value' => $schema->minimum]);
                if ($schema->exclusiveMinimum && $element <= $schema->minimum) {
                    $this->addError($path, $errorMsg, 'exclusiveMinimum', array('minimum' => $schema->minimum,));
                } else if ($element < $schema->minimum) {
                    $this->addError($path, $errorMsg, 'minimum', array('minimum' => $schema->minimum,));
                }
            } else {
                $errorMsg = I18n::t("constraints.number.exclusive_minimum");
                $this->addError($path, $errorMsg, 'missingMinimum');
            }
        } else if (isset($schema->minimum) && $element < $schema->minimum) {
            $errorMsg = I18n::t("constraints.number.minimum", ['value' => $schema->minimum]);
            $this->addError($path, $errorMsg, 'minimum', array('minimum' => $schema->minimum,));
        }

        // Verify maximum
        if (isset($schema->exclusiveMaximum)) {
            if (isset($schema->maximum)) {
                $errorMsg = I18n::t("constraints.number.maximum", ['value' => $schema->maximum]);
                if ($schema->exclusiveMaximum && $element >= $schema->maximum) {
                    $this->addError($path, $errorMsg, 'exclusiveMaximum', array('maximum' => $schema->maximum,));
                } else if ($element > $schema->maximum) {
                    $this->addError($path, $errorMsg, 'maximum', array('maximum' => $schema->maximum,));
                }
            } else {
                $this->addError($path, "Use of exclusiveMaximum requires presence of maximum", 'missingMaximum');
            }
        } else if (isset($schema->maximum) && $element > $schema->maximum) {
            $errorMsg = I18n::t("constraints.number.maximum", ['value' => $schema->maximum]);
            $this->addError($path, $errorMsg, 'maximum', array('maximum' => $schema->maximum,));
        }

        // Verify divisibleBy - Draft v3
        if (isset($schema->divisibleBy) && $this->fmod($element, $schema->divisibleBy) != 0) {
            $errorMsg = I18n::t("constraints.number.divisible_by", ['value' => $schema->divisibleBy]);
            $this->addError($path, $errorMsg, 'divisibleBy', array('divisibleBy' => $schema->divisibleBy,));
        }

        // Verify multipleOf - Draft v4
        if (isset($schema->multipleOf) && $this->fmod($element, $schema->multipleOf) != 0) {
            $errorMsg = I18n::t("constraints.number.multiple_of", ['value' => $schema->multipleOf]);
            $this->addError($path, $errorMsg, 'multipleOf', array('multipleOf' => $schema->multipleOf,));
        }

        $this->checkFormat($element, $schema, $path, $i);
    }

    private function fmod($number1, $number2)
    {
        $modulus = fmod($number1, $number2);
        $precision = abs(0.0000000001);
        $diff = (float)($modulus - $number2);

        if (-$precision < $diff && $diff < $precision) {
            return 0.0;
        }

        $decimals1 = mb_strpos($number1, ".") ? mb_strlen($number1) - mb_strpos($number1, ".") - 1 : 0;
        $decimals2 = mb_strpos($number2, ".") ? mb_strlen($number2) - mb_strpos($number2, ".") - 1 : 0;

        return (float)round($modulus, max($decimals1, $decimals2));
    }
}
