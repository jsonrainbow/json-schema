<?php

namespace JsonSchema;

use JsonSchema\Exception\InvalidArgumentException;

class ConstraintError extends Enum
{
    const ADDITIONAL_ITEMS = 'additionalItems';
    const ADDITIONAL_PROPERTIES = 'additionalProp';
    const ALL_OF = 'allOf';
    const ANY_OF = 'anyOf';
    const DEPENDENCIES = 'dependencies';
    const DISALLOW = 'disallow';
    const DIVISIBLE_BY = 'divisibleBy';
    const ENUM = 'enum';
    const CONSTANT = 'const';
    const EXCLUSIVE_MINIMUM = 'exclusiveMinimum';
    const EXCLUSIVE_MAXIMUM = 'exclusiveMaximum';
    const FORMAT_COLOR = 'colorFormat';
    const FORMAT_DATE = 'dateFormat';
    const FORMAT_DATE_TIME = 'dateTimeFormat';
    const FORMAT_DATE_UTC = 'dateUtcFormat';
    const FORMAT_EMAIL = 'emailFormat';
    const FORMAT_HOSTNAME = 'styleHostName';
    const FORMAT_IP = 'ipFormat';
    const FORMAT_PHONE = 'phoneFormat';
    const FORMAT_REGEX= 'regexFormat';
    const FORMAT_STYLE = 'styleFormat';
    const FORMAT_TIME = 'timeFormat';
    const FORMAT_URL = 'urlFormat';
    const FORMAT_URL_REF = 'urlRefFormat';
    const INVALID_SCHEMA = 'invalidSchema';
    const LENGTH_MAX = 'maxLength';
    const LENGTH_MIN = 'minLength';
    const MAXIMUM = 'maximum';
    const MIN_ITEMS = 'minItems';
    const MINIMUM = 'minimum';
    const MISSING_ERROR = 'missingError';
    const MISSING_MAXIMUM = 'missingMaximum';
    const MISSING_MINIMUM = 'missingMinimum';
    const MAX_ITEMS = 'maxItems';
    const MULTIPLE_OF = 'multipleOf';
    const NOT = 'not';
    const ONE_OF = 'oneOf';
    const REQUIRED = 'required';
    const REQUIRES = 'requires';
    const PATTERN = 'pattern';
    const PREGEX_INVALID = 'pregrex';
    const PROPERTIES_MIN = 'minProperties';
    const PROPERTIES_MAX = 'maxProperties';
    const TYPE = 'type';
    const UNIQUE_ITEMS = 'uniqueItems';

    public function getMessage()
    {
        $name = $this->getValue();
        static $messages = array(
            self::ADDITIONAL_ITEMS => 'The item %s[%s] is not defined and the definition does not allow additional items',
            self::ADDITIONAL_PROPERTIES => 'The property %s is not defined and the definition does not allow additional properties',
            self::ALL_OF => 'Failed to match all schemas',
            self::ANY_OF => 'Failed to match at least one schema',
            self::DEPENDENCIES => '%s depends on %s, which is missing',
            self::DISALLOW => 'Disallowed value was matched',
            self::DIVISIBLE_BY => 'Is not divisible by %d',
            self::ENUM => 'Does not have a value in the enumeration %s',
            self::CONSTANT => 'Does not have a value equal to %s',
            self::EXCLUSIVE_MINIMUM => 'Must have a minimum value greater than %d',
            self::EXCLUSIVE_MAXIMUM => 'Must have a maximum value less than %d',
            self::FORMAT_COLOR => 'Invalid color',
            self::FORMAT_DATE => 'Invalid date %s, expected format YYYY-MM-DD',
            self::FORMAT_DATE_TIME => 'Invalid date-time %s, expected format YYYY-MM-DDThh:mm:ssZ or YYYY-MM-DDThh:mm:ss+hh:mm',
            self::FORMAT_DATE_UTC => 'Invalid time %s, expected integer of milliseconds since Epoch',
            self::FORMAT_EMAIL => 'Invalid email',
            self::FORMAT_HOSTNAME => 'Invalid hostname',
            self::FORMAT_IP => 'Invalid IP address',
            self::FORMAT_PHONE => 'Invalid phone number',
            self::FORMAT_REGEX=> 'Invalid regex format %s',
            self::FORMAT_STYLE => 'Invalid style',
            self::FORMAT_TIME => 'Invalid time %s, expected format hh:mm:ss',
            self::FORMAT_URL => 'Invalid URL format',
            self::FORMAT_URL_REF => 'Invalid URL reference format',
            self::LENGTH_MAX => 'Must be at most %d characters long',
            self::INVALID_SCHEMA => 'Schema is not valid',
            self::LENGTH_MIN => 'Must be at least %d characters long',
            self::MAX_ITEMS => 'There must be a maximum of %d items in the array',
            self::MAXIMUM => 'Must have a maximum value less than or equal to %d',
            self::MIN_ITEMS => 'There must be a minimum of %d items in the array',
            self::MINIMUM => 'Must have a minimum value greater than or equal to %d',
            self::MISSING_MAXIMUM => 'Use of exclusiveMaximum requires presence of maximum',
            self::MISSING_MINIMUM => 'Use of exclusiveMinimum requires presence of minimum',
            /*self::MISSING_ERROR => 'Used for tests; this error is deliberately commented out',*/
            self::MULTIPLE_OF => 'Must be a multiple of %s',
            self::NOT => 'Matched a schema which it should not',
            self::ONE_OF => 'Failed to match exactly one schema',
            self::REQUIRED => 'The property %s is required',
            self::REQUIRES => 'The presence of the property %s requires that %s also be present',
            self::PATTERN => 'Does not match the regex pattern %s',
            self::PREGEX_INVALID => 'The pattern %s is invalid',
            self::PROPERTIES_MIN => 'Must contain a minimum of %d properties',
            self::PROPERTIES_MAX => 'Must contain no more than %d properties',
            self::TYPE => '%s value found, but %s is required',
            self::UNIQUE_ITEMS => 'There are no duplicates allowed in the array'
        );

        if (!isset($messages[$name])) {
            throw new InvalidArgumentException('Missing error message for ' . $name);
        }

        return $messages[$name];
    }
}
