<?php

declare(strict_types=1);

namespace JsonSchema\Tool\Validator;

/**
 * Matches the URI and URI-reference grammar of RFC 3986, section 3 and 4.1.
 *
 * @internal
 */
class Rfc3986
{
    public static function isUri(string $value): bool
    {
        return self::matches($value, false);
    }

    public static function isUriReference(string $value): bool
    {
        return self::matches($value, true);
    }

    private static function matches(string $value, bool $allowRelative): bool
    {
        $unreservedSubDelims = 'A-Za-z0-9\-._~!$&\'()*+,;=';
        // Matches runs of characters possessively, keeping long values within the PCRE backtracking limits
        $chars = static function (string $extra) use ($unreservedSubDelims): string {
            return '(?:[' . $unreservedSubDelims . $extra . ']++|%[0-9A-Fa-f]{2})';
        };

        $scheme = '[A-Za-z][A-Za-z0-9+\-.]*+';
        $ipvFuture = '[vV][0-9A-Fa-f]++\.[' . $unreservedSubDelims . ':]++';
        $authority = '(?:' . $chars(':') . '*+@)?(?:\[(?:[0-9A-Fa-f:.]++|' . $ipvFuture . ')\]|' . $chars('') . '*+)(?::[0-9]*+)?';
        $pathAbempty = '(?:\/' . $chars(':@') . '*+)*+';
        $pathAbsolute = '\/(?:' . $chars(':@') . '++' . $pathAbempty . ')?';
        $pathRootless = $chars(':@') . '++' . $pathAbempty;
        $pathNoScheme = $chars('@') . '++' . $pathAbempty;
        $query = '(?:\?' . $chars(':@\/?') . '*+)?';
        $fragment = '(?:#' . $chars(':@\/?') . '*+)?';

        $hierPart = '(?:\/\/' . $authority . $pathAbempty . '|' . $pathAbsolute . '|' . $pathRootless . '|)';
        $relativePart = '(?:\/\/' . $authority . $pathAbempty . '|' . $pathAbsolute . '|' . $pathNoScheme . '|)';
        $pattern = $allowRelative
            ? '(?:' . $scheme . ':' . $hierPart . '|' . $relativePart . ')'
            : $scheme . ':' . $hierPart;

        if (preg_match('/^' . $pattern . $query . $fragment . '\z/', $value) !== 1) {
            return false;
        }

        // The grammar only checks the characters of an IP literal, its address is checked separately
        if (preg_match('/^(?:' . $scheme . ':)?\/\/(?:[^\/?#@]*+@)?\[(?<ipLiteral>[0-9A-Fa-f:.]++)\]/', $value, $matches) === 1) {
            return filter_var($matches['ipLiteral'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }

        return true;
    }
}
