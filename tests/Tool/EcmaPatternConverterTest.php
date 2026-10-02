<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Tool;

use JsonSchema\Tool\EcmaPatternConverter;
use PHPUnit\Framework\TestCase;

class EcmaPatternConverterTest extends TestCase
{
    /** @dataProvider matchingDataProvider */
    public function testConvertedPatternMatchesLikeEcma(string $pattern, string $subject, bool $expectedToMatch): void
    {
        self::assertSame($expectedToMatch ? 1 : 0, preg_match(EcmaPatternConverter::toPcre($pattern), $subject));
    }

    public static function matchingDataProvider(): \Generator
    {
        yield 'ASCII digit matches \d' => ['pattern' => '^\d+$', 'subject' => '42', 'expectedToMatch' => true];
        yield 'Arabic-Indic digit does not match \d' => ['pattern' => '^\d+$', 'subject' => '٣', 'expectedToMatch' => false];
        yield 'Arabic-Indic digit matches \D' => ['pattern' => '^\D$', 'subject' => '٣', 'expectedToMatch' => true];
        yield 'accented letter does not match \w' => ['pattern' => '^\w+$', 'subject' => 'é', 'expectedToMatch' => false];
        yield 'accented letter matches \W' => ['pattern' => '^\W$', 'subject' => 'é', 'expectedToMatch' => true];
        yield 'zero width space matches \s' => ['pattern' => '^\s$', 'subject' => "\u{200B}", 'expectedToMatch' => true];
        yield '\w inside a character class' => ['pattern' => '^[\w-]+$', 'subject' => 'a-b_1', 'expectedToMatch' => true];
        yield '\w inside a character class stays ASCII' => ['pattern' => '^[\w]+$', 'subject' => 'é', 'expectedToMatch' => false];
        yield '\d inside a character class' => ['pattern' => '^[\d.]+$', 'subject' => '1.2', 'expectedToMatch' => true];
        yield '\d inside a negated character class' => ['pattern' => '^[^\d]+$', 'subject' => 'abc', 'expectedToMatch' => true];
        yield '\D inside a character class' => ['pattern' => '^[\D]+$', 'subject' => 'abc', 'expectedToMatch' => true];
        yield '\D inside a character class excludes ASCII digits' => ['pattern' => '^[\D]+$', 'subject' => 'a1', 'expectedToMatch' => false];
        yield '\W inside a character class' => ['pattern' => '^[\W]+$', 'subject' => '-é', 'expectedToMatch' => true];
        yield '\W inside a character class excludes word characters' => ['pattern' => '^[\W]+$', 'subject' => '_', 'expectedToMatch' => false];
        yield 'escaped backslash before d' => ['pattern' => '^\\\\d$', 'subject' => '\d', 'expectedToMatch' => true];
        yield 'escaped closing bracket inside a character class' => ['pattern' => '^[\]\d]+$', 'subject' => ']1', 'expectedToMatch' => true];
        yield 'unescaped slash' => ['pattern' => '^a/b$', 'subject' => 'a/b', 'expectedToMatch' => true];
        yield 'escaped slash' => ['pattern' => '^a\/b$', 'subject' => 'a/b', 'expectedToMatch' => true];
        yield 'ECMA long property name for digits' => ['pattern' => '^\p{digit}$', 'subject' => '٣', 'expectedToMatch' => true];
        yield 'ECMA long property name for letters' => ['pattern' => '^\p{Letter}$', 'subject' => 'é', 'expectedToMatch' => true];
    }
}
