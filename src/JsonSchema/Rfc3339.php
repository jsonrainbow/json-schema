<?php

namespace JsonSchema;

class Rfc3339
{
    const REGEX = '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})(\.\d+)?(Z|([+-]\d{2}):?(\d{2}))$/';

    /**
     * Try creating a DateTime instance
     *
     * @param string $string
     *
     * @return \DateTime|null
     */
    public static function createFromString($string)
    {
        if (!preg_match(self::REGEX, strtoupper($string), $matches)) {
            return null;
        }

        $dateAndTime = $matches[1];
        $microseconds = $matches[2] ?: '.000000';
        $timeZone = 'Z' !== $matches[3] ? $matches[4] . ':' . $matches[5] : '+00:00';

        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $dateAndTime . $microseconds . $timeZone, new \DateTimeZone('UTC'));

        return $dateTime ?: null;
    }
}
