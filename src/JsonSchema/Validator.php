<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Entity\JsonPointer;

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
    public function check($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('schema');
        $validator->check($value, $schema);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));
    }

    /**
     * Does everything that check does, but will also coerce string values in the input to their expected
     * types defined in the schema whenever possible. Note that the first argument is passed by reference,
     * so you must pass in a variable.
     *
     * {@inheritDoc}
     */
    public function coerce(&$value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('schema');
        $validator->coerce($value, $schema);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));
    }
}
