<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Constraints\Schema;
use JsonSchema\Constraints\Constraint;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Exception\InvalidSchemaMediaTypeException;
use JsonSchema\Exception\JsonDecodingException;

use JsonSchema\Uri\Retrievers\UriRetrieverInterface;

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
        if ($this->checkMode === self::CHECK_MODE_ARR_AS_OBJ) {
            $value = $this->convertFromAllArrays($value);
            $schema = $this->convertFromAllArrays($schema);
        }
        $validator = new Schema($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Converts the result of a json_encode($data, true) to a json_encode($data, false)
     *
     * @param mixed $data
     * @throws InvalidArgumentException
     * @return mixed
     */
    private function convertFromAllArrays($data)
    {
        if (
            is_null($data) ||
            is_bool($data) ||
            is_int($data) ||
            is_float($data) ||
            is_string($data)
        ) {
            return $data;
        }

        if (is_object($data) || is_resource($data)) {
            throw new InvalidArgumentException('Found an object or resource when CHECK_MODE_ARR_AS_OBJ was set.');
        }

        // only array is left...

        foreach ($data as &$v) {
            $v = $this->convertFromAllArrays($v);
        }

        if (!$this->isArrayObjectLike($data)) {
            return $data;
        }

        return (object) $data;
    }

    /**
     * Determines if the given array looks like a JSON object
     *
     * @param array $data
     * @return bool
     */
    private function isArrayObjectLike(array $data)
    {
        $objectLike = false;
        $i = 0;
        foreach ($data as $k => $v) {
            if ($i !== $k) {
                $objectLike = true;
                return $objectLike;
            }
            $i++;
        }
        return $objectLike;
    }
}
