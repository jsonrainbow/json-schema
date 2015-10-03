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
                    $this->addError($path, sprintf('Invalid date %s, expected format YYYY-MM-DD', json_encode($element)));
                }
                break;

            case 'time':
                if (!$this->validateDateTime($element, 'H:i:s')) {
                    $this->addError($path, sprintf('Invalid time %s, expected format hh:mm:ss', json_encode($element)));
                }
                break;

            case 'date-time':
                if (!$this->validateDateTime($element, 'Y-m-d\TH:i:s\Z') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:s.u\Z') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:sP') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:sO')
                ) {
                    $this->addError($path, sprintf('Invalid date-time %s, expected format YYYY-MM-DDThh:mm:ssZ or YYYY-MM-DDThh:mm:ss+hh:mm', json_encode($element)));
                }
                break;

            case 'utc-millisec':
                if (!$this->validateDateTime($element, 'U')) {
                    $this->addError($path, sprintf('Invalid time %s, expected integer of milliseconds since Epoch', json_encode($element)));
                }
                break;

            case 'regex':
                if (!$this->validateRegex($element)) {
                    $this->addError($path, 'Invalid regex format ' . $element);
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
                if (!filter_var($element, FILTER_CALLBACK, array('options' => array($this, 'validateRfc3986Uri')))) {
                    $this->addError($path, "Invalid URI format");
                }
                break;

            case 'email':
                if (null === filter_var($element, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)) {
                    $this->addError($path, "Invalid email");
                }
                break;

            case 'ip-address':
            case 'ipv4':
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
            case 'hostname':
                if (!$this->validateHostname($element)) {
                    $this->addError($path, "Invalid hostname");
                }
                break;

            default:
                // Do nothing so that custom formats can be used.
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

    /**
     * Validates URI according to rfc3986.
     * @see https://gist.github.com/kpobococ/92f120c6c4a9a52b84e3
     * @param string $uri
     * @return bool
     */
    protected function validateRfc3986Uri($uri)
    {
        // Play around with this regexp online:
        // http://regex101.com/r/hZ5gU9/1
        $regex = '`

        # URI scheme RFC 3986 (http://tools.ietf.org/html/rfc3986)

        (?(DEFINE)

          # ABNF notation of RFC 2234 (http://tools.ietf.org/html/rfc2234#section-6.1)

          (?<ALPHA>     [\x41-\x5A\x61-\x7A] )    # Latin character (A-Z, a-z)
          (?<CR>        \x0D )                    # Carriage return (\r)
          (?<DIGIT>     [\x30-\x39] )             # Decimal number (0-9)
          (?<DQUOTE>    \x22 )                    # Double quote (")
          (?<HEXDIG>    (?&DIGIT) | [\x41-\x46] ) # Hexadecimal number (0-9, A-F)
          (?<LF>        \x0A )                    # Line feed (\n)
          (?<SP>        \x20 )                    # Space

          # RFC 3986 body

          (?<uri>    (?&scheme) \: (?&hier_part) (?: \? (?&query) )? (?: \# (?&fragment) )? )

          (?<hier_part>    \/\/ (?&authority) (?&path_abempty)
                         | (?&path_absolute)
                         | (?&path_rootless)
                         | (?&path_empty) )

          (?<uri_reference>    (?&uri) | (?&relative_ref) )

          (?<absolute_uri>    (?&scheme) \: (?&hier_part) (?: \? (?&query) )? )

          (?<relative_ref>    (?&relative_part) (?: \? (?&query) )? (?: \# (?&fragment) )? )

          (?<relative_part>     \/\/ (?&authority) (?&path_abempty)
                              | (?&path_absolute)
                              | (?&path_noscheme)
                              | (?&path_empty) )

          (?<scheme>    (?&ALPHA) (?: (?&ALPHA) | (?&DIGIT) | \+ | \- | \. )* )

          (?<authority>    (?: (?&userinfo) \@ )? (?&host) (?: \: (?&port) )? )
          (?<userinfo>     (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \: )* )
          (?<host>         (?&ip_literal) | (?&ipv4_address) | (?&reg_name) )
          (?<port>         (?&DIGIT)* )

          (?<ip_literal>    \[ (?: (?&ipv6_address) | (?&ipv_future) ) \] )

          (?<ipv_future>    \x76 (?&HEXDIG)+ \. (?: (?&unreserved) | (?&sub_delims) | \: )+ )

          (?<ipv6_address>                                              (?: (?&h16) \: ){6} (?&ls32)
                            |                                      \:\: (?: (?&h16) \: ){5} (?&ls32)
                            |                           (?&h16)?   \:\: (?: (?&h16) \: ){4} (?&ls32)
                            | (?: (?: (?&h16) \: ){0,1} (?&h16) )? \:\: (?: (?&h16) \: ){3} (?&ls32)
                            | (?: (?: (?&h16) \: ){0,2} (?&h16) )? \:\: (?: (?&h16) \: ){2} (?&ls32)
                            | (?: (?: (?&h16) \: ){0,3} (?&h16) )? \:\:     (?&h16) \:      (?&ls32)
                            | (?: (?: (?&h16) \: ){0,4} (?&h16) )? \:\:                     (?&ls32)
                            | (?: (?: (?&h16) \: ){0,5} (?&h16) )? \:\:                     (?&h16)
                            | (?: (?: (?&h16) \: ){0,6} (?&h16) )? \:\: )

          (?<h16>             (?&HEXDIG){1,4} )
          (?<ls32>            (?: (?&h16) \: (?&h16) ) | (?&ipv4_address) )
          (?<ipv4_address>    (?&dec_octet) \. (?&dec_octet) \. (?&dec_octet) \. (?&dec_octet) )

          (?<dec_octet>    (?&DIGIT)
                         | [\x31-\x39] (?&DIGIT)
                         | \x31 (?&DIGIT){2}
                         | \x32 [\x30-\x34] (?&DIGIT)
                         | \x32\x35 [\x30-\x35] )

          (?<reg_name>     (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) )* )

          (?<path>    (?&path_abempty)
                    | (?&path_absolute)
                    | (?&path_noscheme)
                    | (?&path_rootless)
                    | (?&path_empty) )

          (?<path_abempty>     (?: \/ (?&segment) )* )
          (?<path_absolute>    \/ (?: (?&segment_nz) (?: \/ (?&segment) )* )? )
          (?<path_noscheme>    (?&segment_nz_nc) (?: \/ (?&segment) )* )
          (?<path_rootless>    (?&segment_nz) (?: \/ (?&segment) )* )
          (?<path_empty>       (?&pchar){0} ) # For explicity only

          (?<segment>       (?&pchar)* )
          (?<segment_nz>    (?&pchar)+ )
          (?<segment_nz_nc> (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \@ )+ )

          (?<pchar>    (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \: | \@ )

          (?<query>    (?: (?&pchar) | \/ | \? )* )

          (?<fragment>    (?: (?&pchar) | \/ | \? )* )

          (?<pct_encoded>    \% (?&HEXDIG) (?&HEXDIG) )

          (?<unreserved>    (?&ALPHA) | (?&DIGIT) | \- | \. | \_ | \~ )
          (?<reserved>      (?&gen_delims) | (?&sub_delims) )
          (?<gen_delims>    \: | \/ | \? | \# | \[ | \] | \@ )
          (?<sub_delims>    \! | \$ | \& | \' | \( | \)
                          | \* | \+ | \, | \; | \= )

        )

        ^(?&uri)$

        `x';

        return preg_match($regex, $uri) === 1;
    }
}
