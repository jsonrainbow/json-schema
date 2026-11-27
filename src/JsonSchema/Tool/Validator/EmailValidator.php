<?php

declare(strict_types=1);

namespace JsonSchema\Tool\Validator;

/**
 * Validates an e-mail address as the Mailbox of RFC 5321, section 4.1.2.
 */
class EmailValidator
{
    public static function isValid(string $email): bool
    {
        $at = strrpos($email, '@');
        if ($at === false) {
            return false;
        }

        $localPart = substr($email, 0, $at);
        $domain = substr($email, $at + 1);

        return self::isValidLocalPart($localPart) && (self::isValidDomain($domain) || self::isValidAddressLiteral($domain));
    }

    private static function isValidLocalPart(string $localPart): bool
    {
        $atext = '[A-Za-z0-9!#$%&\'*+\-\/=?^_`{|}~]';
        $dotString = $atext . '++(?:\.' . $atext . '++)*+';
        // qtextSMTP and quoted-pairSMTP, both limited to printable US-ASCII
        $quotedString = '"(?:[\x20\x21\x23-\x5B\x5D-\x7E]|\\\\[\x20-\x7E])*+"';

        return strlen($localPart) <= 64 && preg_match('/^(?:' . $dotString . '|' . $quotedString . ')\z/', $localPart) === 1;
    }

    private static function isValidDomain(string $domain): bool
    {
        $label = '[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?';

        return strlen($domain) <= 255 && preg_match('/^' . $label . '(?:\.' . $label . ')*\z/', $domain) === 1;
    }

    private static function isValidAddressLiteral(string $domain): bool
    {
        if (preg_match('/^\[(?:(?<ipv4>[0-9.]++)|IPv6:(?<ipv6>[0-9A-Fa-f:.]++))\]\z/i', $domain, $matches) !== 1) {
            return false;
        }

        if (isset($matches['ipv6']) && $matches['ipv6'] !== '') {
            return filter_var($matches['ipv6'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }

        return filter_var($matches['ipv4'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }
}
