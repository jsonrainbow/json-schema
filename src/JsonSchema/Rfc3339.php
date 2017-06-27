<?php

namespace JsonSchema;

/**
 * Helper for creating DateTime objects from an RFC-3339 string
 *
 * @package justinrainbow\json-schema
 *
 * @license MIT
 */
class Rfc3339
{
    /** The regular expression used to tokenize the RFC-3339 date-time string */
    const REGEX = '/^(\d{4}-\d{2}-\d{2}[T ]{1}\d{2}:\d{2}:\d{2})(\.\d+)?(Z|([+-]\d{2}):?(\d{2}))$/';

    /**
     * Create a DateTime object based on an RFC-3339 string
     *
     * @param string $string The string to parse
     *
     * @return \DateTime|null A DateTime object corresponding to the given string, or null on error
     */
    public static function createFromString($string)
    {
        if (!preg_match(self::REGEX, strtoupper($string), $matches)) {
            return null;
        }

        $dateAndTime = $matches[1];
        $microseconds = $matches[2] ?: '.000000';
        $timeZone = 'Z' !== $matches[3] ? $matches[4] . ':' . $matches[5] : '+00:00';
        $dateFormat = strpos($dateAndTime, 'T') === false ? 'Y-m-d H:i:s.uP' : 'Y-m-d\TH:i:s.uP';
        $dateTime = \DateTime::createFromFormat($dateFormat, $dateAndTime . $microseconds . $timeZone, new \DateTimeZone('UTC'));

        return $dateTime ?: null;
    }
}
