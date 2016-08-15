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
    protected $schemaStorage;
    protected $checkMode = self::CHECK_MODE_NORMAL;
    protected $uriRetriever;
    protected $errors = array();
    protected $inlineSchemaProperty = '$schema';

    const CHECK_MODE_NORMAL = 1;
    const CHECK_MODE_TYPE_CAST = 2;

    /**
     * @var null|Factory
     */
    private $factory;

    /**
     * @param int $checkMode
     * @param SchemaStorage $schemaStorage
     * @param UriRetrieverInterface $uriRetriever
     * @param Factory $factory
     */
    public function __construct(
        $checkMode = self::CHECK_MODE_NORMAL,
        SchemaStorage $schemaStorage = null,
        UriRetrieverInterface $uriRetriever = null,
        Factory $factory = null
    ) {
        $this->checkMode     = $checkMode;
        $this->uriRetriever  = $uriRetriever;
        $this->factory       = $factory;
        $this->schemaStorage = $schemaStorage;
    }

    /**
     * @return UriRetrieverInterface $uriRetriever
     */
    public function getUriRetriever()
    {
        if (is_null($this->uriRetriever)) {
            $this->setUriRetriever(new UriRetriever);
        }

        return $this->uriRetriever;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        if (!$this->factory) {
            $this->factory = new Factory($this->getSchemaStorage(), $this->getUriRetriever(), $this->checkMode);
        }

        return $this->factory;
    }

    /**
     * @return SchemaStorage
     */
    public function getSchemaStorage()
    {
        if (is_null($this->schemaStorage)) {
            $this->schemaStorage = new SchemaStorage($this->getUriRetriever());
        }

        return $this->schemaStorage;
    }

    /**
     * @param UriRetrieverInterface $uriRetriever
     */
    public function setUriRetriever(UriRetrieverInterface $uriRetriever)
    {
        $this->uriRetriever = $uriRetriever;
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
        $this->errors = array_merge($this->errors, $errors);
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
        $validator = $this->getFactory()->createInstanceFor('collection');
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
        $validator = $this->getFactory()->createInstanceFor('object');
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
        $validator = $this->getFactory()->createInstanceFor('type');
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
        $validator = $this->getFactory()->createInstanceFor('undefined');
        $validator->check($value, $this->schemaStorage->resolveRefSchema($schema), $path, $i);

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
        $validator = $this->getFactory()->createInstanceFor('string');
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
        $validator = $this->getFactory()->createInstanceFor('number');
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
        $validator = $this->getFactory()->createInstanceFor('enum');
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
        $validator = $this->getFactory()->createInstanceFor('format');
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
        return $this->getFactory()->getTypeCheck();
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
