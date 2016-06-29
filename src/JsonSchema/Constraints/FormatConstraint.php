<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Rfc3339;
use robotdance\I18n;

/**
 * Validates against the "format" property
 *
 * @author Justin Rainbow <justin.rainbow@gmail.com>
 * @link   http://tools.ietf.org/html/draft-zyp-json-schema-03#section-5.23
 */
class FormatConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        if (!isset($schema->format)) {
            return;
        }

        switch ($schema->format) {
            case 'date':
                if (!$date = $this->validateDateTime($element, 'Y-m-d')) {
                    $errorMsg = I18n::t("constraints.format.date", ['date' => json_encode($element)]);
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'time':
                if (!$this->validateDateTime($element, 'H:i:s')) {
                    $errorMsg = I18n::t("constraints.format.time", ['time' => json_encode($element)]);
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'date-time':
                if (null === Rfc3339::createFromString($element)) {
                    $errorMsg = I18n::t("constraints.format.datetime", ['datetime' => json_encode($element)]);
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'utc-millisec':
                if (!$this->validateDateTime($element, 'U')) {
                    $errorMsg = I18n::t("constraints.format.utcmilli", ['utcmilli' => json_encode($element)]);
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'regex':
                if (!$this->validateRegex($element)) {
                    $errorMsg = I18n::t("constraints.format.regex", ['regex' => json_encode($element)]);
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'color':
                if (!$this->validateColor($element)) {
                    $errorMsg = I18n::t("constraints.format.color");
                    $this->addError($path,  $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'style':
                if (!$this->validateStyle($element)) {
                    $errorMsg = I18n::t("constraints.format.style");
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'phone':
                if (!$this->validatePhone($element)) {
                    $errorMsg = I18n::t("constraints.format.phone");
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'uri':
                if (null === filter_var($element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE)) {
                    $errorMsg = I18n::t("constraints.format.url");
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'email':
                if (null === filter_var($element, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)) {
                    $errorMsg = I18n::t("constraints.format.email");
                    $this->addError($path, "Invalid email", 'format', array('format' => $schema->format,));
                }
                break;

            case 'ip-address':
            case 'ipv4':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV4)) {
                    $errorMsg = I18n::t("constraints.format.ipv4");
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'ipv6':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV6)) {
                    $errorMsg = I18n::t("constraints.format.ipv6");
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
                }
                break;

            case 'host-name':
            case 'hostname':
                if (!$this->validateHostname($element)) {
                    $errorMsg = I18n::t("constraints.format.hostname");
                    $this->addError($path, $errorMsg, 'format', array('format' => $schema->format,));
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

        // handles the case where a non-6 digit microsecond datetime is passed
        // which will fail the above string comparison because the passed
        // $datetime may be '2000-05-01T12:12:12.123Z' but format() will return
        // '2000-05-01T12:12:12.123000Z'
        if ((strpos('u', $format) !== -1) && (preg_match('/\.\d+Z$/', $datetime))) {
            return true;
        }

        return false;
    }

    protected function validateRegex($regex)
    {
        return false !== @preg_match('/' . $regex . '/', '');
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
        return preg_match('/^[_a-z]+\.([_a-z]+\.?)+$/i', $host);
    }
}
