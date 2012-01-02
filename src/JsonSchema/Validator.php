<?php

namespace JsonSchema;

use JsonSchema\Constraints\Schema;
use JsonSchema\Constraints\Constraint;

/**
 * A JsonSchema Constraint
 *
 * @see README.md
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Validator extends Constraint
{
    /**
     * validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org
     *
     * {inheritDoc}
     */
    function check($value, $schema = null, $path = null, $i = null)
    {
        $validator = new Schema($this->checkMode);
        $validator->check($value, $schema);
        $this->addErrors($validator->getErrors());
    }
}