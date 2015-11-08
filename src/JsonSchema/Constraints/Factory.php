<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

/**
 * Factory for centralize constraint initialization.
 */
class Factory
{
    /**
     * @var UriRetriever
     */
    protected $uriRetriever;

    /**
     * @param UriRetriever $uriRetriever
     */
    public function __construct(UriRetriever $uriRetriever = null)
    {
        if (!$uriRetriever) {
            $uriRetriever = new UriRetriever();
        }

        $this->uriRetriever = $uriRetriever;
    }

    /**
     * @return UriRetriever
     */
    public function getUriRetriever()
    {
        return $this->uriRetriever;
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
        switch ($constraintName) {
            case 'array':
            case 'collection':
                return new CollectionConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'object':
                return new ObjectConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'type':
                return new TypeConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'undefined':
                return new UndefinedConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'string':
                return new StringConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'number':
                return new NumberConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'enum':
                return new EnumConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'format':
                return new FormatConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'schema':
                return new SchemaConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            case 'validator':
                return new Validator(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
        }

        throw new InvalidArgumentException('Unknown constraint ' . $constraintName);
    }
}
