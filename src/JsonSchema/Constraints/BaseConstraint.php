<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\TypeCheck\LooseTypeCheck;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\ValidationException;

/**
 * A more basic constraint definition - used for the public
 * interface to avoid exposing library internals.
 */
class BaseConstraint
{
    /**
     * @var array Errors
     */
    protected $errors = array();

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
    }

    public function addError(ConstraintError $constraint, JsonPointer $path = null, array $more = array())
    {
        $message = $constraint ? $constraint->getMessage() : '';
        $name = $constraint ? $constraint->getValue() : '';
        $error = array(
            'property' => $this->convertJsonPointerIntoPropertyPath($path ?: new JsonPointer('')),
            'pointer' => ltrim(strval($path ?: new JsonPointer('')), '#'),
            'message' => ucfirst(vsprintf($message, array_map(function ($val) {
                if (is_scalar($val)) {
                    return $val;
                }

                return json_encode($val);
            }, array_values($more)))),
            'constraint' => array(
                'name' => $name,
                'params' => $more
            )
        );

        if ($this->factory->getConfig(Constraint::CHECK_MODE_EXCEPTIONS)) {
            throw new ValidationException(sprintf('Error validating %s: %s', $error['pointer'], $error['message']));
        }

        $this->errors[] = $error;
    }

    public function addErrors(array $errors)
    {
        if ($errors) {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

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
     * Recursively cast an associative array to an object
     *
     * @param array $array
     *
     * @return object
     */
    public static function arrayToObjectRecursive($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::arrayToObjectRecursive($value);
            }
        }

        return LooseTypeCheck::isObject($array) ? (object) $array : $array;
    }
}
