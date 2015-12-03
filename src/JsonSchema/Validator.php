<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\InvalidArgumentException;

/**
 * A JsonSchema Constraint
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 * @see    README.md
 */
class Validator extends Constraint
{
    const SCHEMA_MEDIA_TYPE = 'application/schema+json';

    /**
     * Validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org
     *
     * {@inheritDoc}
     */
    public function check($value, $schema = null, $path = null, $i = null)
    {
        $validator = $this->getFactory()->createInstanceFor('schema');
        $validator->check($value, $schema);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));
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
     * @throws InvalidArgumentException if the $constraint is either not a class or not a ConstraintInterface
     */
    public function addConstraint($name, $constraint)
    {
        $this->getFactory()->addConstraint($name, $constraint);
    }
}
