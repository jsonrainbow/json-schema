<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Exception\InvalidArgumentException;

/**
 * The Type Constraints, validates an element against a given type
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Type extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($value = null, $schema = null, $path = null, $i = null)
    {
        $type = isset($schema->type) ? $schema->type : null;
        $isValid = true;

        if (is_array($type)) {
            // @TODO refactor
            $validatedOneType = false;
            $errors = array();
            foreach ($type as $tp) {
                $validator = new Type($this->checkMode);
                $subSchema = new \stdClass();
                $subSchema->type = $tp;
                $validator->check($value, $subSchema, $path, null);
                $error = $validator->getErrors();

                if (!count($error)) {
                    $validatedOneType = true;
                    break;
                }

                $errors = $error;
            }

            if (!$validatedOneType) {
                return $this->addErrors($errors);
            }
        } elseif (is_object($type)) {
            $this->checkUndefined($value, $type, $path);
        } else {
            $isValid = $this->validateType($value, $type);
        }

        if ($isValid === false) {
            $this->addError($path, gettype($value) . " value found, but a " . $type . " is required");
        }
    }

    /**
     * Verifies that a given value is of a certain type
     *
     * @param mixed  $value Value to validate
     * @param string $type  Type to check against
     *
     * @return boolean
     *
     * @throws InvalidArgumentException
     */
    protected function validateType($value, $type)
    {
        //mostly the case for inline schema
        if (!$type) {
            return true;
        }

        if ('integer' === $type) {
            return is_int($value);
        }

        if ('number' === $type) {
            return is_numeric($value) && !is_string($value);
        }

        if ('boolean' === $type) {
            return is_bool($value);
        }

        if ('object' === $type) {
            return is_object($value);
            //return ($this::CHECK_MODE_TYPE_CAST == $this->checkMode) ? is_array($value) : is_object($value);
        }

        if ('array' === $type) {
            return is_array($value);
        }

        if ('string' === $type) {
            return is_string($value);
        }

        if ('null' === $type) {
            return is_null($value);
        }

        if ('any' === $type) {
            return true;
        }

        throw new InvalidArgumentException((is_object($value) ? 'object' : $value) . ' is an invalid type for ' . $type);
    }
}