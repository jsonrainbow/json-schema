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
use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Exception\ValidationException;
use JsonSchema\Validator;

/**
 * The base constraint definition from which all other constraint classes descend
 *
 * This class is also used as the base class for Validator, in order to expose some
 * common functionality to end users of the library.
 *
 * @package justinrainbow/json-schema
 *
 * @license MIT
 */
class BaseConstraint
{
    /**
     * @var array List of errors encountered during validation against the current constraint
     */
    protected $errors = array();

    /**
     * @var int Bitwise list of all error categories in which a validation error has occurred
     */
    protected $errorMask = Validator::ERROR_NONE;

    /**
     * @var Factory Factory object containing global state, config options & misc functionality
     */
    protected $factory;

    /**
     * Create a new constraint instance
     *
     * @api via JsonSchema\Validator
     *
     * @param Factory $factory Factory object containing global state, config options & misc functionality
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
    }

    /**
     * Add an error to the list of errors encountered during validation
     *
     * @param ConstraintError $constraint Which error condition has been encountered
     * @param JsonPointer     $path       Where the error occurred
     * @param array           $more       List of additional parameters used to generate error messages
     */
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
            ),
            'context' => $this->factory->getErrorContext(),
        );

        if ($this->factory->getConfig(Constraint::CHECK_MODE_EXCEPTIONS)) {
            throw new ValidationException(sprintf('Error validating %s: %s', $error['pointer'], $error['message']));
        }

        $this->errors[] = $error;
        $this->errorMask |= $error['context'];
    }

    /**
     * Add multiple, already-rendered errors to the current list for this constraint
     *
     * @param array $errors List of errors to add
     */
    public function addErrors(array $errors)
    {
        if ($errors) {
            $this->errors = array_merge($this->errors, $errors);
            $errorMask = &$this->errorMask;
            array_walk($errors, function ($error) use (&$errorMask) {
                if (isset($error['context'])) {
                    $errorMask |= $error['context'];
                }
            });
        }
    }

    /**
     * Get a list of all errors encountered during validation.
     *
     * If you only want to return errors in particular categories, set $errorContext to
     * include the desired categories.
     *
     * @api via JsonSchema\Validator
     *
     * @param int $errorContext Which categories of error to include
     *
     * @return array List of errors
     */
    public function getErrors($errorContext = Validator::ERROR_ALL)
    {
        if ($errorContext === Validator::ERROR_ALL) {
            return $this->errors;
        }

        return array_filter($this->errors, function ($error) use ($errorContext) {
            if ($errorContext & $error['context']) {
                return true;
            }
        });
    }

    /**
     * Return the number of errors encountered during validation.
     *
     * If you only want to count the errors in particular categories, set $errorContext to
     * include the desired categories.
     *
     * @api via JsonSchema\Validator
     *
     * @param int $errorContext Which categories of error to include
     *
     * @return int Number of errors
     */
    public function numErrors($errorContext = Validator::ERROR_ALL)
    {
        if ($errorContext === Validator::ERROR_ALL) {
            return count($this->errors);
        }

        return count($this->getErrors($errorContext));
    }

    /**
     * Check whether the most recent validation attempt completed successfully
     *
     * @api via JsonSchema\Validator
     *
     * @return bool True if no errors were encountered, false otherwise
     */
    public function isValid()
    {
        return !$this->getErrors();
    }

    /**
     * Clear the list of reported errors. Should be used between multiple validation attempts.
     *
     * reset() is called automatically when using JsonSchema\Validator::validate().
     */
    public function reset()
    {
        $this->errors = array();
        $this->errorMask = Validator::ERROR_NONE;
    }

    /**
     * Get a list of categories in which a validation error has occurred
     *
     * @api via JsonSchema\Validator
     *
     * @return int Bitwise list of error categories that were encountered
     */
    public function getErrorMask()
    {
        return $this->errorMask;
    }

    /**
     * Recursively cast an associative array to an object
     *
     * @param array $array The array to cast
     *
     * @return object The cast object
     */
    public static function arrayToObjectRecursive($array)
    {
        $json = json_encode($array);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            $message = 'Unable to encode schema array as JSON';
            if (function_exists('json_last_error_msg')) {
                $message .= ': ' . json_last_error_msg();
            }
            throw new InvalidArgumentException($message);
        }

        return (object) json_decode($json);
    }
}
