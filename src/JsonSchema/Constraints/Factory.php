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
     * @var array
     */
    private $constraints = [];

    /**
     * @param UriRetriever $uriRetriever
     */
    public function __construct(UriRetriever $uriRetriever = null)
    {
        if ( ! $uriRetriever) {
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
     * Add a custom constraint
     *
     * By instance:
     *    $factory->addConstraint('name', new \FQCN(...)); // need to provide own ctr params
     *
     * By class name:
     *    $factory->addConstraint('name', '\FQCN'); // inherits ctr params from current
     *
     * As a \Callable (the Constraint::checks() method):
     *    $factory->addConstraint('name', \Callable); // inherits ctr params from current
     *
     * NOTE: By class-name or as a Callable will inherit the current configuration (uriRetriever, factory)
     *
     * @param string $name
     * @param ConstraintInterface|string|\Callable $constraint
     *
     * @todo possible own exception?
     *
     * @throws InvalidArgumentException if the $constraint is either not a class or not a ConstraintInterface
     */
    public function addConstraint($name, $constraint)
    {

        if (is_callable($constraint)) {
            $this->constraints[$name] = new CallableConstraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever,
                $this, $constraint);

            return;
        }

        if (is_string($constraint)) {
            if ( ! class_exists($constraint)) {
                // @todo possible own exception?
                throw new InvalidArgumentException('Constraint class "' . $constraint . '" is not a Class');
            }
            $constraint = new $constraint(Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
            if ( ! $constraint instanceof ConstraintInterface) {
                // @todo possible own exception?
                throw new InvalidArgumentException('Constraint class "' . get_class($constraint) . '" is not an instance of ConstraintInterface');
            }
        }

        $this->constraints[$name] = $constraint;
    }

    /**
     * @param $constraintName
     *
     * @return bool
     */
    public function hasConstraint($constraintName)
    {
        return ! empty($this->constraints[$constraintName])
               && $this->constraints[$constraintName] instanceof ConstraintInterface;
    }

    /**
     * Create a constraint instance for the given constraint name.
     *
     * @param string $constraintName
     *
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

        if ($this->hasConstraint($constraintName)) {
            return $this->constraints[$constraintName];
        }

        throw new InvalidArgumentException('Unknown constraint ' . $constraintName);
    }

}
