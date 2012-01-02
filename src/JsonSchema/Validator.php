<?php

namespace JsonSchema;

use JsonSchema\Validator\Schema;
use JsonSchema\Validator\Validator as BaseValidator;

/**
 * A JsonSchema Validator
 *
 * @see README.md
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Validator extends BaseValidator
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