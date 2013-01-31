<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

/**
 * Validates against the "format" property
 *
 * @author Justin Rainbow <justin.rainbow@gmail.com>
 * @link   http://tools.ietf.org/html/draft-zyp-json-schema-03#section-5.23
 */
class Format extends Constraint
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
                    $this->addError($path, sprintf('Invalid date %s, expected format YYYY-MM-DD', json_encode($element)));
                }
                break;

            case 'time':
                if (!$this->validateDateTime($element, 'H:i:s')) {
                    $this->addError($path, sprintf('Invalid time %s, expected format HH:MM:SS', json_encode($element)));
                }
                break;

            case 'date-time':
                if (!$this->validateDateTime($element, 'Y-m-d\TH:i:s\Z') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:sP')
                ) {
                    $this->addError($path, sprintf('Invalid date time %s, expected format YYYY-MM-DDTHH:MM:SSZ or YYYY-MM-DDTHH:MM:SS+HH:MM', json_encode($element)));
                }
                break;

            case 'utc-millisec':
                if (!$this->validateDateTime($element, 'U')) {
                    $this->addError($path, sprintf('Invalid time %s, expected integer of milliseconds since Epoch', json_encode($element)));
                }
                break;

            case 'regex':
                if (!$this->validateRegex($element, $schema->pattern)) {
                    $this->addError($path, "Invalid regex format");
                }
                break;

            case 'color':
                if (!$this->validateColor($element)) {
                    $this->addError($path, "Invalid color");
                }
                break;

            case 'style':
                if (!$this->validateStyle($element)) {
                    $this->addError($path, "Invalid style");
                }
                break;

            case 'phone':
                if (!$this->validatePhone($element)) {
                    $this->addError($path, "Invalid phone number");
                }
                break;

            case 'uri':
                if (null === filter_var($element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE)) {
                    $this->addError($path, "Invalid URL format");
                }
                break;

            case 'email':
                if (null === filter_var($element, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)) {
                    $this->addError($path, "Invalid email");
                }
                break;

            case 'ip-address':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV4)) {
                    $this->addError($path, "Invalid IP address");
                }
                break;

            case 'ipv6':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV6)) {
                    $this->addError($path, "Invalid IP address");
                }
                break;

            case 'host-name':
                if (!$this->validateHostname($element)) {
                    $this->addError($path, "Invalid hostname");
                }
                break;

            default:
                $this->addError($path, "Unknown format: " . json_encode($schema->format));
                break;
        }
    }

    protected function validateDateTime($datetime, $format)
    {
        $dt = \DateTime::createFromFormat($format, $datetime);

        if (!$dt) {
            return false;
        }

        return $datetime === $dt->format($format);
    }

    protected function validateRegex($string, $regex)
    {
        return preg_match('/' . $regex . '/', $string);
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
