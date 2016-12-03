<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\SchemaStorage;
use JsonSchema\SchemaStorageInterface;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\UriRetrieverInterface;

/**
 * Factory for centralize constraint initialization.
 */
class Factory
{
    /**
     * @var SchemaStorage
     */
    protected $schemaStorage;

    /**
     * @var UriRetriever $uriRetriever
     */
    protected $uriRetriever;

    /**
     * @var int
     */
    private $checkMode;

    /**
     * @var TypeCheck\TypeCheckInterface[]
     */
    private $typeCheck = array();

    /**
     * @var array $constraintMap
     */
    protected $constraintMap = array(
        'array' => 'JsonSchema\Constraints\CollectionConstraint',
        'collection' => 'JsonSchema\Constraints\CollectionConstraint',
        'object' => 'JsonSchema\Constraints\ObjectConstraint',
        'type' => 'JsonSchema\Constraints\TypeConstraint',
        'undefined' => 'JsonSchema\Constraints\UndefinedConstraint',
        'string' => 'JsonSchema\Constraints\StringConstraint',
        'number' => 'JsonSchema\Constraints\NumberConstraint',
        'enum' => 'JsonSchema\Constraints\EnumConstraint',
        'format' => 'JsonSchema\Constraints\FormatConstraint',
        'schema' => 'JsonSchema\Constraints\SchemaConstraint',
        'validator' => 'JsonSchema\Validator',
        'coercer' => 'JsonSchema\Coerce'
    );

    /**
     * @var array<ConstraintInterface>
     */
    private $instanceCache = array();

    /**
     * @param SchemaStorage $schemaStorage
     * @param UriRetrieverInterface $uriRetriever
     * @param int $checkMode
     */
    public function __construct(
        SchemaStorageInterface $schemaStorage = null,
        UriRetrieverInterface $uriRetriever = null,
        $checkMode = Constraint::CHECK_MODE_NORMAL
    ) {
        $this->uriRetriever = $uriRetriever ?: new UriRetriever;
        $this->schemaStorage = $schemaStorage ?: new SchemaStorage($this->uriRetriever);
        $this->checkMode = $checkMode;
    }

    /**
     * @return UriRetrieverInterface
     */
    public function getUriRetriever()
    {
        return $this->uriRetriever;
    }

    public function getSchemaStorage()
    {
        return $this->schemaStorage;
    }

    public function getTypeCheck()
    {
        if (!isset($this->typeCheck[$this->checkMode])) {
            $this->typeCheck[$this->checkMode] = ($this->checkMode & Constraint::CHECK_MODE_TYPE_CAST)
                ? new TypeCheck\LooseTypeCheck
                : new TypeCheck\StrictTypeCheck;
        }

        return $this->typeCheck[$this->checkMode];
    }

    /**
     * @param string $name
     * @param string $class
     * @return Factory
     */
    public function setConstraintClass($name, $class)
    {
        // Ensure class exists
        if (!class_exists($class)) {
            throw new InvalidArgumentException('Unknown constraint ' . $name);
        }
        // Ensure class is appropriate
        if (!in_array('JsonSchema\Constraints\ConstraintInterface', class_implements($class))) {
            throw new InvalidArgumentException('Invalid class ' . $name);
        }
        $this->constraintMap[$name] = $class;
        return $this;
    }

    /**
     * Create a constraint instance for the given constraint name.
     *
     * @param string $constraintName
     * @return ConstraintInterface|ObjectConstraint
     * @throws InvalidArgumentException if is not possible create the constraint instance.
     */
    public function createInstanceFor($constraintName)
    {
        if (!isset($this->constraintMap[$constraintName])) {
            throw new InvalidArgumentException('Unknown constraint ' . $constraintName);
        }

        if (!isset($this->instanceCache[$constraintName])) {
            $this->instanceCache[$constraintName] = new $this->constraintMap[$constraintName]($this);
        }

        return clone $this->instanceCache[$constraintName];
    }

    /**
     * @return int
     */
    public function getCheckMode()
    {
        return $this->checkMode;
    }
}
