<?php

namespace JsonSchema;

use JsonSchema\Exception\InvalidPointerException;
use JsonSchema\Exception\ResourceNotFoundException;

/**
 * Resolve JSON Pointers (RFC 6901)
 */
class PointerResolver
{
    const EMPTY_ELEMENT = '_empty_';
    const LAST_ELEMENT = '-';
    const SEPARATOR = '/';

    /**
     * Get the part of the document which the pointer points to.
     *
     * @param object $json    The json document to resolve within.
     * @param string $pointer The Json Pointer.
     *
     * @throws InvalidPointerException
     * @throws ResourceNotFoundException
     *
     * @return mixed
     */
    public function resolvePointer($json, $pointer)
    {
        if ($pointer === '') {
            return $json;
        }

        $this->validatePointer($pointer);

        $parts = array_slice(array_map('urldecode', explode('/', $pointer)), 1);

        return $this->resolve($json, $pointer, $this->decodeParts($parts));
    }

    /**
     * Decode any escaped sequences.
     *
     * @param array $parts The json pointer parts.
     *
     * @return array
     */
    private function decodeParts(array $parts)
    {
        $mappings = array(
            '~1' => '/',
            '~0' => '~',
        );

        foreach ($parts as &$part) {
            $part = strtr($part, $mappings);
        }

        return $parts;
    }

    /**
     * Recurse through $json until location described by $parts is found.
     *
     * @param mixed  $json  The json document.
     * @param string $json  The original json pointer.
     * @param array  $parts The (remaining) parts of the pointer.
     *
     * @throws ResourceNotFoundException
     *
     * @return mixed
     */
    private function resolve($json, $pointer, array $parts)
    {
        // Check for completion
        if (count($parts) === 0) {
            return $json;
        }

        $part = array_shift($parts);

        // Ensure we deal with empty keys the same way as json_decode does
        if ($part === '') {
            $part = self::EMPTY_ELEMENT;
        }

        if (is_object($json) && property_exists($json, $part)) {
            return $this->resolve($json->$part, $pointer, $parts);
        } elseif (is_array($json)) {
            if ($part === self::LAST_ELEMENT) {
                return $this->resolve(end($json), $pointer, $parts);
            }
            if (filter_var($part, FILTER_VALIDATE_INT) !== false &&
                array_key_exists($part, $json)
            ) {
                return $this->resolve($json[$part], $pointer, $parts);
            }
        }

        $message = "Failed to resolve pointer $pointer from document id"
            . (isset($json->id) ? $json->id : '');
        throw new ResourceNotFoundException($message);
    }

    /**
     * Validate a pointer string.
     *
     * @param string $pointer The pointer to validate.
     *
     * @throws InvalidPointerException
     */
    private function validatePointer($pointer)
    {
        if ($pointer !== '' && !is_string($pointer)) {
            throw new InvalidPointerException('Pointer is not a string');
        }

        $firstCharacter = substr($pointer, 0, 1);

        if ($firstCharacter !== self::SEPARATOR) {
            throw new InvalidPointerException('Pointer starts with invalid character');
        }
    }

}
