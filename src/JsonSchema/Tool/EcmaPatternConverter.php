<?php

declare(strict_types=1);

namespace JsonSchema\Tool;

/**
 * Turns an ECMA-262 pattern, as used by "pattern" and "patternProperties", into a PCRE regex.
 *
 * The pattern is walked rather than rewritten with str_replace(), so that an escaped backslash
 * is never read as the start of a shorthand class, and so that a shorthand class inside a
 * bracket expression ("[\w-]") is expanded into ranges instead of a nested bracket.
 *
 * @internal
 */
class EcmaPatternConverter
{
    /**
     * PCRE with /u makes \d, \D, \w and \W Unicode aware, while ECMA-262 defines them over ASCII
     * only, so they are narrowed back to their ECMA meaning. Each entry is the content of a
     * character class, to be wrapped in brackets when used outside of one.
     */
    private const SHORTHAND_CLASSES = [
        'd' => '0-9',
        'D' => '\x{0}-\x{2F}\x{3A}-\x{10FFFF}',
        'w' => 'A-Za-z0-9_',
        'W' => '\x{0}-\x{2F}\x{3A}-\x{40}\x{5B}-\x{5E}\x{60}\x{7B}-\x{10FFFF}',
        's' => '\s\x{200B}', // Explicitly include zero width white space
    ];

    /**
     * PCRE rejects the ECMA long property names, so they are mapped to its abbreviations.
     */
    private const PROPERTY_NAMES = [
        '\p{digit}' => '\p{Nd}',
        '\p{Letter}' => '\p{L}',
    ];

    public static function toPcre(string $pattern): string
    {
        $regex = '';
        $inClass = false;
        $length = strlen($pattern);

        for ($i = 0; $i < $length; $i++) {
            $char = $pattern[$i];

            if ($char === '\\' && $i + 1 < $length) {
                $next = $pattern[$i + 1];

                if (isset(self::SHORTHAND_CLASSES[$next])) {
                    $regex .= $inClass ? self::SHORTHAND_CLASSES[$next] : '[' . self::SHORTHAND_CLASSES[$next] . ']';
                    $i++;
                    continue;
                }

                foreach (self::PROPERTY_NAMES as $ecma => $pcre) {
                    if (substr_compare($pattern, $ecma, $i, strlen($ecma)) === 0) {
                        $regex .= $pcre;
                        $i += strlen($ecma) - 1;
                        continue 2;
                    }
                }

                // Any other escape, an escaped backslash or delimiter included, is kept as is.
                $regex .= $char . $next;
                $i++;
                continue;
            }

            if ($char === '[' && !$inClass) {
                $inClass = true;
            } elseif ($char === ']' && $inClass) {
                $inClass = false;
            } elseif ($char === '/') {
                $char = '\/';
            }

            $regex .= $char;
        }

        return '/' . $regex . '/u';
    }
}
