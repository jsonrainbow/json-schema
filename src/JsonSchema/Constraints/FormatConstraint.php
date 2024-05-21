<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Rfc3339;

/**
 * Validates against the "format" property
 *
 * @author Justin Rainbow <justin.rainbow@gmail.com>
 *
 * @see   http://tools.ietf.org/html/draft-zyp-json-schema-03#section-5.23
 */
class FormatConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function check(&$element, $schema = null, JsonPointer $path = null, $i = null)
    {
        if (!isset($schema->format) || $this->factory->getConfig(self::CHECK_MODE_DISABLE_FORMAT)) {
            return;
        }

        switch ($schema->format) {
            case 'date':
                if (!$date = $this->validateDateTime($element, 'Y-m-d')) {
                    $this->addError(ConstraintError::FORMAT_DATE(), $path, array(
                            'date' => $element,
                            'format' => $schema->format
                        )
                    );
                }
                break;

            case 'time':
                if (!$this->validateDateTime($element, 'H:i:s')) {
                    $this->addError(ConstraintError::FORMAT_TIME(), $path, array(
                            'time' => json_encode($element),
                            'format' => $schema->format,
                        )
                    );
                }
                break;

            case 'date-time':
                if (null === Rfc3339::createFromString($element)) {
                    $this->addError(ConstraintError::FORMAT_DATE_TIME(), $path, array(
                            'dateTime' => json_encode($element),
                            'format' => $schema->format
                        )
                    );
                }
                break;

            case 'utc-millisec':
                if (!$this->validateDateTime($element, 'U')) {
                    $this->addError(ConstraintError::FORMAT_DATE_UTC(), $path, array(
                        'value' => $element,
                        'format' => $schema->format));
                }
                break;

            case 'regex':
                if (!$this->validateRegex($element)) {
                    $this->addError(ConstraintError::FORMAT_REGEX(), $path, array(
                            'value' => $element,
                            'format' => $schema->format
                        )
                    );
                }
                break;

            case 'color':
                if (!$this->validateColor($element)) {
                    $this->addError(ConstraintError::FORMAT_COLOR(), $path, array('format' => $schema->format));
                }
                break;

            case 'style':
                if (!$this->validateStyle($element)) {
                    $this->addError(ConstraintError::FORMAT_STYLE(), $path, array('format' => $schema->format));
                }
                break;

            case 'phone':
                if (!$this->validatePhone($element)) {
                    $this->addError(ConstraintError::FORMAT_PHONE(), $path, array('format' => $schema->format));
                }
                break;

            case 'uri':
                if (null === filter_var($element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE)) {
                    $this->addError(ConstraintError::FORMAT_URL(), $path, array('format' => $schema->format));
                }
                break;

            case 'uriref':
            case 'uri-reference':
                if (null === filter_var($element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE)) {
                    // FILTER_VALIDATE_URL does not conform to RFC-3986, and cannot handle relative URLs, but
                    // the json-schema spec uses RFC-3986, so need a bit of hackery to properly validate them.
                    // See https://tools.ietf.org/html/rfc3986#section-4.2 for additional information.
                    if (substr($element, 0, 2) === '//') { // network-path reference
                        $validURL = filter_var('scheme:' . $element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
                    } elseif (substr($element, 0, 1) === '/') { // absolute-path reference
                        $validURL = filter_var('scheme://host' . $element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
                    } elseif (strlen($element)) { // relative-path reference
                        $pathParts = explode('/', $element, 2);
                        if (strpos($pathParts[0], ':') !== false) {
                            $validURL = null;
                        } else {
                            $validURL = filter_var('scheme://host/' . $element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
                        }
                    } else {
                        $validURL = null;
                    }
                    if ($validURL === null) {
                        $this->addError(ConstraintError::FORMAT_URL_REF(), $path, array('format' => $schema->format));
                    }
                }
                break;

            case 'email':
                $filterFlags = FILTER_NULL_ON_FAILURE;
                if (defined('FILTER_FLAG_EMAIL_UNICODE')) {
                    // Only available from PHP >= 7.1.0, so ignore it for coverage checks
                    $filterFlags |= constant('FILTER_FLAG_EMAIL_UNICODE'); // @codeCoverageIgnore
                }
                if (null === filter_var($element, FILTER_VALIDATE_EMAIL, $filterFlags)) {
                    $this->addError(ConstraintError::FORMAT_EMAIL(), $path, array('format' => $schema->format));
                }
                break;

            case 'ip-address':
            case 'ipv4':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV4)) {
                    $this->addError(ConstraintError::FORMAT_IP(), $path, array('format' => $schema->format));
                }
                break;

            case 'ipv6':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV6)) {
                    $this->addError(ConstraintError::FORMAT_IP(), $path, array('format' => $schema->format));
                }
                break;

            case 'host-name':
            case 'hostname':
                if (!$this->validateHostname($element)) {
                    $this->addError(ConstraintError::FORMAT_HOSTNAME(), $path, array('format' => $schema->format));
                }
                break;

            default:
                // Empty as it should be:
                // The value of this keyword is called a format attribute. It MUST be a string.
                // A format attribute can generally only validate a given set of instance types.
                // If the type of the instance to validate is not in this set, validation for
                // this format attribute and instance SHOULD succeed.
                // http://json-schema.org/latest/json-schema-validation.html#anchor105
                break;
        }
    }

    protected function validateDateTime($datetime, $format)
    {
        $dt = \DateTime::createFromFormat($format, $datetime);

        if (!$dt) {
            return false;
        }

        if ($datetime === $dt->format($format)) {
            return true;
        }

        return false;
    }

    protected function validateRegex($regex)
    {
        return false !== @preg_match(self::jsonPatternToPhpRegex($regex), '');
    }

    protected function validateColor($color)
    {
        if (in_array(strtolower($color), array('aqua', 'black', 'blue', 'fuchsia',
            'gray', 'green', 'lime', 'maroon', 'navy', 'olive', 'orange', 'purple',
            'red', 'silver', 'teal', 'white', 'yellow'))) {
            return true;
        }

        return preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color);
    }

    protected function validateStyle($style)
    {
        $properties     = explode(';', rtrim($style, ';'));
        $invalidEntries = preg_grep('/^\s*[-a-z]+\s*:\s*.+$/i', $properties, PREG_GREP_INVERT);

        return empty($invalidEntries);
    }

    protected function validatePhone($phone)
    {
        return preg_match('/^\+?(\(\d{3}\)|\d{3}) \d{3} \d{4}$/', $phone);
    }

    protected function validateHostname($host)
    {
        $hostnameRegex = '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/i';

        return preg_match($hostnameRegex, $host);
    }
}
