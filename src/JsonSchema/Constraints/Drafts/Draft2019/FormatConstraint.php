<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

use DateTimeZone;
use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Rfc3339;
use JsonSchema\Tool\Validator\RelativeReferenceValidator;
use JsonSchema\Tool\Validator\UriValidator;

class FormatConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    public function __construct(?Factory $factory = null)
    {
        $this->initialiseErrorBag($factory ?: new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'format')) {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        switch ($schema->format) {
            case 'date':
                if (!$this->validateDateTime($value, 'Y-m-d')) {
                    $this->addError(ConstraintError::FORMAT_DATE(), $path, ['date' => $value, 'format' => $schema->format]);
                }
                break;
            case 'time':
                if (!$this->validateDateTime($value, 'H:i:sP')
                    && !$this->validateDateTime($value, 'H:i:sp')
                    && !$this->validateDateTime($value, 'H:i:s.up')
                    && !$this->validateDateTime($value, 'H:i:s.uP')
                ) {
                    $this->addError(ConstraintError::FORMAT_TIME(), $path, ['time' => $value, 'format' => $schema->format]);
                }
                break;
            case 'date-time':
                if (!$this->validateRfc3339DateTime($value)) {
                    $this->addError(ConstraintError::FORMAT_DATE_TIME(), $path, ['dateTime' => $value, 'format' => $schema->format]);
                }
                break;
            case 'utc-millisec':
                if (!$this->validateDateTime($value, 'U')) {
                    $this->addError(ConstraintError::FORMAT_DATE_UTC(), $path, ['value' => $value, 'format' => $schema->format]);
                }
                break;
            case 'duration':
                if (!$this->validateDuration($value)) {
                    $this->addError(ConstraintError::FORMAT_DURATION(), $path, ['value' => $value, 'format' => $schema->format]);
                }
                break;
            case 'regex':
                if (!$this->validateRegex($value)) {
                    $this->addError(ConstraintError::FORMAT_REGEX(), $path, ['value' => $value, 'format' => $schema->format]);
                }
                break;
            case 'ip-address':
            case 'ipv4':
                if (filter_var($value, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV4) === null) {
                    $this->addError(ConstraintError::FORMAT_IP(), $path, ['format' => $schema->format]);
                }
                break;
            case 'ipv6':
                if (filter_var($value, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV6) === null) {
                    $this->addError(ConstraintError::FORMAT_IP(), $path, ['format' => $schema->format]);
                }
                break;
            case 'color':
                if (!$this->validateColor($value)) {
                    $this->addError(ConstraintError::FORMAT_COLOR(), $path, ['format' => $schema->format]);
                }
                break;
            case 'style':
                if (!$this->validateStyle($value)) {
                    $this->addError(ConstraintError::FORMAT_STYLE(), $path, ['format' => $schema->format]);
                }
                break;
            case 'phone':
                if (!$this->validatePhone($value)) {
                    $this->addError(ConstraintError::FORMAT_PHONE(), $path, ['format' => $schema->format]);
                }
                break;
            case 'uri':
                if (!UriValidator::isValid($value)) {
                    $this->addError(ConstraintError::FORMAT_URL(), $path, ['format' => $schema->format]);
                }
                break;

            case 'uriref':
            case 'uri-reference':
                if (!(UriValidator::isValid($value) || RelativeReferenceValidator::isValid($value))) {
                    $this->addError(ConstraintError::FORMAT_URL(), $path, ['format' => $schema->format]);
                }
                break;
            case 'iri':
                if (!$this->validateIri($value, false)) {
                    $this->addError(ConstraintError::FORMAT_URL(), $path, ['format' => $schema->format]);
                }
                break;
            case 'iri-reference':
                if (!$this->validateIri($value, true)) {
                    $this->addError(ConstraintError::FORMAT_URL_REF(), $path, ['format' => $schema->format]);
                }
                break;
            case 'uri-template':
                if (!$this->validateUriTemplate($value)) {
                    $this->addError(ConstraintError::FORMAT_URI_TEMPLATE(), $path, ['format' => $schema->format]);
                }
                break;
            case 'uuid':
                if (!$this->validateUuid($value)) {
                    $this->addError(ConstraintError::FORMAT_UUID(), $path, ['format' => $schema->format]);
                }
                break;
            case 'email':
                if (filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE | FILTER_FLAG_EMAIL_UNICODE) === null) {
                    $this->addError(ConstraintError::FORMAT_EMAIL(), $path, ['format' => $schema->format]);
                }
                break;
            case 'host-name':
            case 'hostname':
                if (!$this->validateHostname($value)) {
                    $this->addError(ConstraintError::FORMAT_HOSTNAME(), $path, ['format' => $schema->format]);
                }
                break;
            case 'idn-hostname':
                if (!$this->validateInternationalizedHostname($value)) {
                    $this->addError(ConstraintError::FORMAT_HOSTNAME(), $path, ['format' => $schema->format]);
                }
                break;
            case 'json-pointer':
                if (!$this->validateJsonPointer($value)) {
                    $this->addError(ConstraintError::FORMAT_JSON_POINTER(), $path, ['format' => $schema->format]);
                }
                break;
            default:
                break;
        }
    }

    private function validateDateTime(string $datetime, string $format): bool
    {
        $datetime = strtoupper($datetime); // Cleanup for lowercase z
        $isPhpLt80WithZulu = PHP_VERSION_ID < 80000 && substr($datetime, -1) === 'Z';
        $isLeap = substr($datetime, 6, 2) === '60';
        $input = $datetime;

        // Correct for Zulu in PHP < 8.0
        if ($isPhpLt80WithZulu) {
            $input = sprintf('%s+00:00', substr($input, 0, -1));
        }
        // Correct for leap second
        if ($isLeap) {
            $input = sprintf('%s59%s', substr($datetime, 0, 6), substr($datetime, 8));
        }

        try {
            $dt = \DateTimeImmutable::createFromFormat($format, $input);
        } catch (\Throwable $e) {
            return false;
        }

        if (!$dt) {
            return false;
        }

        // Handle invalid timezone offsets
        $timezoneOffset = $dt->getTimezone()->getOffset($dt);
        if ($timezoneOffset >= 86400 || $timezoneOffset <= -86400) {
            return false;
        }

        $expected = $dt->format($format);
        // Correct for trailing zeros on microseconds
        if ($format === 'H:i:s.up' || $format === 'H:i:s.uP') {
            $expected = sprintf(
                '%s%s',
                rtrim($dt->format('H:i:s.u'), '0'),
                $dt->format(substr($format, -1))
            );
        }
        // Correct back for leap seconds
        if ($isLeap) {
            // Only when 23:59:59 in UTC
            $utcDT = $dt->setTimezone(new DateTimeZone('UTC'));
            if ($utcDT->format('H:i:s') !== '23:59:59') {
                return false;
            }

            $expected = sprintf('%s60%s', substr($expected, 0, 6), substr($expected, 8));
        }
        // Correct back for PHP > 8.0 and Zulu
        if ($isPhpLt80WithZulu) {
            $expected = sprintf('%sZ', substr($expected, 0, -6));
        }

        return $datetime === $expected;
    }

    private function validateRegex(string $regex): bool
    {
        return preg_match(self::jsonPatternToPhpRegex($regex), '') !== false;
    }

    /**
     * Transform a JSON pattern into a PCRE regex
     */
    private static function jsonPatternToPhpRegex(string $pattern): string
    {
        return '~' . str_replace('~', '\\~', $pattern) . '~u';
    }

    private function validateColor(string $color): bool
    {
        if (in_array(strtolower($color), ['aqua', 'black', 'blue', 'fuchsia',
            'gray', 'green', 'lime', 'maroon', 'navy', 'olive', 'orange', 'purple',
            'red', 'silver', 'teal', 'white', 'yellow'])) {
            return true;
        }

        return preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color) === 1;
    }

    private function validateStyle(string $style): bool
    {
        $properties     = explode(';', rtrim($style, ';'));
        $invalidEntries = preg_grep('/^\s*[-a-z]+\s*:\s*.+$/i', $properties, PREG_GREP_INVERT);

        return empty($invalidEntries);
    }

    private function validatePhone(string $phone): bool
    {
        return preg_match('/^\+?(\(\d{3}\)|\d{3}) \d{3} \d{4}$/', $phone) === 1;
    }

    private function validateHostname(string $host): bool
    {
        $hostnameRegex = '/^(?!-)(?!.*?[^A-Za-z0-9\-\.])(?:(?!-)[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?\.)*(?!-)[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?$/';

        return preg_match($hostnameRegex, $host) === 1;
    }

    private function validateInternationalizedHostname(string $host): bool
    {
        if ($host === '') {
            return false;
        }
        $host = rtrim($host, '.');
        $labels = explode('.', $host);
        $asciiLabels = [];

        foreach ($labels as $label) {
            if ($label === '') {
                return false;
            }

            // CONTEXTJ / CONTEXTO checks
            if (
                // Greek KERAIA U+0375
                preg_match('/\x{0375}/u', $label) &&
                !preg_match('/\x{0375}[\x{0370}-\x{03FF}]/u', $label)
            ) {
                return false;
            }

            // Hebrew GERESH / GERSHAYIM U+05F3 / U+05F4
            if (preg_match('/[\x{05F3}\x{05F4}]/u', $label) &&
                !preg_match('/[\x{0590}-\x{05FF}][\x{05F3}\x{05F4}]/u', $label)
            ) {
                return false;
            }

            // Katakana middle dot U+30FB
            if (str_contains($label, "\u{30FB}") &&
                !preg_match('/[\x{30A0}-\x{30FF}]/u', $label)
            ) {
                return false;
            }

            // Arabic digit mixing
            $hasArabicIndic = preg_match('/[\x{0660}-\x{0669}]/u', $label);
            $hasExtArabicIndic = preg_match('/[\x{06F0}-\x{06F9}]/u', $label);
            if ($hasArabicIndic && $hasExtArabicIndic) {
                return false;
            }

            // Devanagari danda U+0964 / U+0965
            if (preg_match('/[\x{0964}\x{0965}]/u', $label) &&
                !preg_match('/[\x{0900}-\x{097F}]/u', $label)
            ) {
                return false;
            }

            // ZWNJ / ZWJ U+200C / U+200D
            if (preg_match('/[\x{200C}\x{200D}]/u', $label)) {
                return false;
            }

            $ascii = idn_to_ascii($label);
            if ($ascii === false) {
                return false;
            }
            // DNS label length
            if (strlen($ascii) > 63) {
                return false;
            }
            // LDH rule (after IDNA)
            if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i', $ascii)) {
                return false;
            }
            $asciiLabels[] = $ascii;
        }

        // Total hostname length (ASCII)
        $asciiHost = implode('.', $asciiLabels);

        return strlen($asciiHost) <= 253;
    }

    private function validateJsonPointer(string $value): bool
    {
        // Must be empty or start with a forward slash
        if ($value !== '' && $value[0] !== '/') {
            return false;
        }

        // Split into reference tokens and check for invalid escape sequences
        $tokens = explode('/', $value);
        array_shift($tokens); // remove leading empty part due to leading slash

        foreach ($tokens as $token) {
            // "~" must only be followed by "0" or "1"
            if (preg_match('/~(?![01])/', $token)) {
                return false;
            }
        }

        return true;
    }

    private function validateRfc3339DateTime(string $value): bool
    {
        $dateTime = Rfc3339::createFromString($value);
        if (is_null($dateTime)) {
            return false;
        }

        // Compare value and date result to be equal
        return true;
    }

    /**
     * Validates an IRI or, when $allowRelative is set, an IRI reference according to the ABNF of RFC 3987 section 2.2.
     */
    private function validateIri(string $value, bool $allowRelative): bool
    {
        // iunreserved and sub-delims; BMP and supplementary ranges are kept in separate classes, as PCRE2 10.46 fails
        // to match some BMP code points when a single class holds too many ranges.
        $bmp = 'A-Za-z0-9\-._~!$&\'()*+,;=\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}';
        $supplementary = '\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}'
            . '\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}'
            . '\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}';
        // Matches runs of characters possessively, keeping long values within the PCRE backtracking limits
        $chars = static function (string $extraBmp, string $extraSupplementary = '') use ($bmp, $supplementary): string {
            return '(?:[' . $bmp . $extraBmp . ']++|[' . $supplementary . $extraSupplementary . ']++|%[0-9A-Fa-f]{2})';
        };

        $scheme = '[A-Za-z][A-Za-z0-9+\-.]*+';
        $ipvFuture = '[vV][0-9A-Fa-f]++\.[A-Za-z0-9\-._~!$&\'()*+,;=:]++';
        $iauthority = '(?:' . $chars(':') . '*+@)?(?:\[(?:(?<ipLiteral>[0-9A-Fa-f:.]++)|' . $ipvFuture . ')\]|' . $chars('') . '*+)(?::[0-9]*+)?';
        $ipathAbempty = '(?:\/' . $chars(':@') . '*+)*+';
        $ipathAbsolute = '\/(?:' . $chars(':@') . '++' . $ipathAbempty . ')?';
        $ipathRootless = $chars(':@') . '++' . $ipathAbempty;
        $ipathNoScheme = $chars('@') . '++' . $ipathAbempty;
        $iquery = '(?:\?' . $chars(':@\/?\x{E000}-\x{F8FF}', '\x{F0000}-\x{FFFFD}\x{100000}-\x{10FFFD}') . '*+)?';
        $ifragment = '(?:#' . $chars(':@\/?') . '*+)?';

        $sharedPart = '(?:\/\/' . $iauthority . $ipathAbempty . '|' . $ipathAbsolute . '|)';
        $pattern = $allowRelative
            ? '(?:(?:' . $scheme . ':)?' . $sharedPart . '|' . $scheme . ':' . $ipathRootless . '|' . $ipathNoScheme . ')'
            : $scheme . ':(?:' . $sharedPart . '|' . $ipathRootless . ')';

        if (preg_match('/^' . $pattern . $iquery . $ifragment . '\z/u', $value, $matches) !== 1) {
            return false;
        }

        if (isset($matches['ipLiteral']) && $matches['ipLiteral'] !== '') {
            return filter_var($matches['ipLiteral'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }

        return true;
    }

    /**
     * Validates a URI template according to the ABNF of RFC 6570 section 2.
     */
    private function validateUriTemplate(string $value): bool
    {
        $pctEncoded = '%[0-9A-Fa-f]{2}';
        // Any Unicode character except CTL, SP, '"', '%', '<', '>', '\', '^', '`', '{', '|' and '}' (ucschar / iprivate).
        // BMP and supplementary ranges are kept in separate classes, as PCRE2 10.46 fails to match some BMP code
        // points when a single class holds too many ranges.
        $literal = '(?:[\x21\x23\x24\x26-\x3B\x3D\x3F-\x5B\x5D\x5F\x61-\x7A\x7E\x{A0}-\x{D7FF}\x{E000}-\x{FDCF}\x{FDF0}-\x{FFEF}]'
            . '|[\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}'
            . '\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}'
            . '\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}\x{F0000}-\x{FFFFD}'
            . '\x{100000}-\x{10FFFD}])';
        $varchar = '(?:[A-Za-z0-9_]|' . $pctEncoded . ')';
        $varspec = $varchar . '(?:\.?' . $varchar . ')*(?::[1-9][0-9]{0,3}|\*)?';
        $expression = '\{[+#.\/;?&]?' . $varspec . '(?:,' . $varspec . ')*\}';

        return preg_match(
            '/^(?:' . $literal . '|' . $pctEncoded . '|' . $expression . ')*\z/u',
            $value
        ) === 1;
    }

    private function validateUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/iD', $value) === 1;
    }

    /**
     * Validates against the duration ABNF from RFC 3339 Appendix A.
     */
    private function validateDuration(string $value): bool
    {
        $time = 'T(?:[0-9]+H(?:[0-9]+M(?:[0-9]+S)?)?|[0-9]+M(?:[0-9]+S)?|[0-9]+S)';
        $date = '(?:[0-9]+D|[0-9]+M(?:[0-9]+D)?|[0-9]+Y(?:[0-9]+M(?:[0-9]+D)?)?)';

        return preg_match('/^P(?:' . $date . '(?:' . $time . ')?|' . $time . '|[0-9]+W)$/D', $value) === 1;
    }
}
