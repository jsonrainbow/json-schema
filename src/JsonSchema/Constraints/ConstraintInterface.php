<?php

namespace JsonSchema\Constraints;

/**
 * The Constraints Interface
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
interface ConstraintInterface
{
    /**
     * returns all collected errors
     *
     * @return array
     */
    function getErrors();

    /**
     * adds errors to this validator
     *
     * @param array $errors
     */
    function addErrors(array $errors);

    /**
     * adds an error
     *
     * @param $path
     * @param $message
     */
    function addError($path, $message);

    /**
     * checks if the validator has not raised errors
     *
     * @return boolean
     */
    function isValid();

    /**
     * invokes the validation of an element
     *
     * @abstract
     * @param mixed $value
     * @param null $schema
     * @param null $path
     * @param null $i
     */
    function check($value, $schema = null, $path = null, $i = null);
}