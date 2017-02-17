<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\InvalidConfigException;

/**
 * A JsonSchema Constraint
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 *
 * @see    README.md
 */
class Validator extends BaseConstraint
{
    const SCHEMA_MEDIA_TYPE = 'application/schema+json';

    /**
     * Validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org.
     *
     * Note that the first argument is passwd by reference, so you must pass in a variable.
     *
     * {@inheritdoc}
     */
    public function validate(&$value, $schema = null, $checkMode = null)
    {
        $initialCheckMode = $this->factory->getConfig();
        if ($checkMode !== null) {
            $this->factory->setConfig($checkMode);
        }

        $validator = $this->factory->createInstanceFor('schema');
        $validator->check($value, $schema);

        $this->factory->setConfig($initialCheckMode);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));
    }

    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API
     */
    public function check($value, $schema)
    {
        return $this->validate($value, $schema);
    }

    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API
     */
    public function coerce(&$value, $schema)
    {
        return $this->validate($value, $schema, Constraint::CHECK_MODE_COERCE_TYPES);
    }
}
