<?php

namespace JsonSchema\Constraints;

/**
 * The Base Constraints, all Validators should extend this class
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
abstract class Constraint implements ConstraintInterface
{
    protected $checkMode = self::CHECK_MODE_NORMAL;
    protected $errors = array();
    protected $inlineSchemaProperty = '$schema';

    const CHECK_MODE_NORMAL = 1;
    const CHECK_MODE_TYPE_CAST = 2;

    /**
     * @param int $checkMode
     */
    public function __construct($checkMode = self::CHECK_MODE_NORMAL)
    {
        $this->checkMode = $checkMode;
    }

    /**
     * {inheritDoc}
     */
    public function addError($path, $message)
    {
        $this->errors[] = array(
            'property' => $path,
            'message' => $message
        );
    }

    /**
     * {inheritDoc}
     */
    public function addErrors(array $errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * {inheritDoc}
     */
    public function getErrors()
    {
        return array_unique($this->errors, SORT_REGULAR);
    }

    /**
     * bubble down the path
     *
     * @param string $path
     * @param mixed $i
     * @return string
     */
    protected function incrementPath($path, $i)
    {
        if ($path !== '') {
            if (is_int($i)) {
                $path .= '[' . $i . ']';
            } else if ($i == '') {
                $path .= '';
            } else {
                $path .= '.' . $i;
            }
        } else {
            $path = $i;
        }

        return $path;
    }

    /**
     * validates an array
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkArray($value, $schema = null, $path = null, $i = null)
    {
        $validator = new Collection($this->checkMode);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * validates an object
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkObject($value, $schema = null, $path = null, $i = null)
    {
        $validator = new Object($this->checkMode);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * validates the type of a property
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkType($value, $schema = null, $path = null, $i = null)
    {
        $validator = new Type($this->checkMode);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * checks a undefined element
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkUndefined($value, $schema = null, $path = null, $i = null)
    {
        $validator = new Undefined($this->checkMode);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * checks a string element
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkString($value, $schema = null, $path = null, $i = null)
    {
        $validator = new String($this->checkMode);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * checks a number element
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkNumber($value, $schema = null, $path = null, $i = null)
    {
        $validator = new Number($this->checkMode);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * checks a enum element
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkEnum($value, $schema = null, $path = null, $i = null)
    {
        $validator = new Enum($this->checkMode);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * {inheritDoc}
     */
    public function isValid()
    {
        return !$this->getErrors();
    }
}