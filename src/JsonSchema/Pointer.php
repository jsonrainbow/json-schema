<?php

namespace JsonSchema;

use JsonSchema\Exception\InvalidPointerException;
use JsonSchema\Exception\ResourceNotFoundException;

/**
 * Resolve JSON Pointers (RFC 6901)
 */
class Pointer
{
    const EMPTY_ELEMENT = '_empty_';
    const LAST_ELEMENT = '-';
    const SEPARATOR = '/';

    /**
     * @var object
     */
    private $json;

    /**
     * @var string
     */
    private $pointer;

    /**
     * @param object $json The json document to resolve within.
     */
    public function __construct($json)
    {
        $this->json = $json;
    }

    /**
     * Get the part of the document which the pointer points to.
     *
     * @param string $pointer The Json Pointer.
     *
     * @throws InvalidPointerException
     * @throws ResourceNotFoundException
     *
     * @return mixed
     */
    public function get($pointer)
    {
        if ($pointer === '') {
            return $this->json;
        }

        $this->validatePointer($pointer);
        $this->pointer = $pointer;

        $parts = array_slice(array_map('urldecode', explode('/', $pointer)), 1);

        return $this->resolve($this->json, $this->decodeParts($parts));
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
     * @param mixed $json  The json document.
     * @param array $parts The parts of the pointer.
     *
     * @throws ResourceNotFoundException
     *
     * @return mixed
     */
    private function resolve($json, array $parts)
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
            return $this->resolve($json->$part, $parts);
        } elseif (is_array($json)) {
            if ($part === self::LAST_ELEMENT) {
                return $this->resolve(end($json), $parts);
            }
            if (filter_var($part, FILTER_VALIDATE_INT) !== false &&
                array_key_exists($part, $json)
            ) {
                return $this->resolve($json[$part], $parts);
            }
        }

        $message = 'Failed to resolve pointer ' . $this->pointer .
            ' from document id ' . (isset($json->id) ? $json->id : '');
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
