<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\UriRetrieverInterface;
use JsonSchema\Entity\JsonPointer;

/**
 * The Base Constraints, all Validators should extend this class
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
abstract class Constraint implements ConstraintInterface
{
    protected $errors = array();
    protected $inlineSchemaProperty = '$schema';

    const CHECK_MODE_NORMAL = 		0x00000001;
    const CHECK_MODE_TYPE_CAST = 	0x00000002;
    const CHECK_MODE_COERCE = 		0x00000004;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ? : new Factory();
    }

    /**
     * {@inheritDoc}
     */
    public function addError(JsonPointer $path = null, $message, $constraint='', array $more=null)
    {
        $error = array(
            'property' => $this->convertJsonPointerIntoPropertyPath($path ?: new JsonPointer('')),
            'pointer' => ltrim(strval($path ?: new JsonPointer('')), '#'),
            'message' => $message,
            'constraint' => $constraint,
        );

        if (is_array($more) && count($more) > 0)
        {
            $error += $more;
        }

        $this->errors[] = $error;
    }

    /**
     * {@inheritDoc}
     */
    public function addErrors(array $errors)
    {
        if ($errors) {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
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
     * Bubble down the path
     *
     * @param JsonPointer|null $path Current path
     * @param mixed            $i    What to append to the path
     *
     * @return JsonPointer;
     */
    protected function incrementPath(JsonPointer $path = null, $i)
    {
        $path = $path ?: new JsonPointer('');
        $path = $path->withPropertyPaths(
            array_merge(
                $path->getPropertyPaths(),
                array_filter(array($i), 'strlen')
            )
        );
        return $path;
    }

    /**
     * Validates an array
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkArray($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('collection');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Validates an object
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     * @param mixed            $patternProperties
     */
    protected function checkObject($value, $schema = null, JsonPointer $path = null, $i = null, $patternProperties = null)
    {
        $validator = $this->factory->createInstanceFor('object');
        $validator->check($value, $schema, $path, $i, $patternProperties);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Validates the type of a property
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkType($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('type');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a undefined element
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkUndefined($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('undefined');

        $validator->check($value, $this->factory->getSchemaStorage()->resolveRefSchema($schema), $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a string element
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkString($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('string');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a number element
     *
     * @param mixed       $value
     * @param mixed       $schema
     * @param JsonPointer $path
     * @param mixed       $i
     */
    protected function checkNumber($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('number');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a enum element
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkEnum($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('enum');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks format of an element
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     */
    protected function checkFormat($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('format');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Get the type check based on the set check mode.
     *
     * @return TypeCheck\TypeCheckInterface
     */
    protected function getTypeCheck()
    {
        return $this->factory->getTypeCheck();
    }

    /**
     * @param JsonPointer $pointer
     * @return string property path
     */
    protected function convertJsonPointerIntoPropertyPath(JsonPointer $pointer)
    {
        $result = array_map(
            function($path) {
                return sprintf(is_numeric($path) ? '[%d]' : '.%s', $path);
            },
            $pointer->getPropertyPaths()
        );
        return trim(implode('', $result), '.');
    }
}
