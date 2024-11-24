<?php

declare(strict_types=1);

namespace JsonSchema\Tests;

use JsonSchema\Rfc3339;
use PHPUnit\Framework\TestCase;

class Rfc3339Test extends TestCase
{
    /**
     * @dataProvider provideValidFormats
     */
    public function testCreateFromValidString(string $string, \DateTime $expected): void
    {
        $actual = Rfc3339::createFromString($string);

        $this->assertInstanceOf('DateTime', $actual);
        $this->assertEquals($expected->format('U.u'), $actual->format('U.u'));
    }

    /**
     * @dataProvider provideInvalidFormats
     */
    public function testCreateFromInvalidString(string $string): void
    {
        $this->assertNull(Rfc3339::createFromString($string), sprintf('String "%s" should not be converted to DateTime', $string));
    }

    public static function provideValidFormats(): \Generator
    {
        yield 'Zulu time' => [
            '2000-05-01T12:12:12Z',
            \DateTime::createFromFormat('Y-m-d\TH:i:s', '2000-05-01T12:12:12', new \DateTimeZone('UTC'))
        ];
        yield 'With time offset - without colon' => [
            '2000-05-01T12:12:12+0100',
            \DateTime::createFromFormat('Y-m-d\TH:i:sP', '2000-05-01T12:12:12+01:00')
        ];
        yield 'With time offset - with colon' => [
            '2000-05-01T12:12:12+01:00',
            \DateTime::createFromFormat('Y-m-d\TH:i:sP', '2000-05-01T12:12:12+01:00')
        ];
        yield 'Zulu time - with microseconds' => [
            '2000-05-01T12:12:12.123456Z',
            \DateTime::createFromFormat('Y-m-d\TH:i:s.u', '2000-05-01T12:12:12.123456', new \DateTimeZone('UTC'))
        ];
        yield 'Zulu time - with milliseconds' => [
            '2000-05-01T12:12:12.123Z',
            \DateTime::createFromFormat('Y-m-d\TH:i:s.u', '2000-05-01T12:12:12.123000', new \DateTimeZone('UTC'))
        ];
        yield 'Zulu time - with milliseconds, without T separator' => [
            '2000-05-01 12:12:12.123Z',
            \DateTime::createFromFormat('Y-m-d H:i:s.u', '2000-05-01 12:12:12.123000', new \DateTimeZone('UTC'))
        ];
        yield 'Zulu time - with microseconds, without T separator' => [
            '2000-05-01 12:12:12.123456Z',
            \DateTime::createFromFormat('Y-m-d H:i:s.u', '2000-05-01 12:12:12.123456', new \DateTimeZone('UTC'))
        ];
    }

    public static function provideInvalidFormats(): \Generator
    {
        yield 'Missing leading zero in month - with T separator' => ['1999-1-11T00:00:00Z'];
        yield 'Missing leading zero in timezone offset - without colon' => ['1999-01-11T00:00:00+100'];
        yield 'Missing leading zero in timezone offset - with colon' => ['1999-01-11T00:00:00+1:00'];
        yield 'Double space between date and time' => ['1999-01-01  00:00:00Z'];
        yield 'Missing leading zero in month - without T separator' => ['1999-1-11 00:00:00Z'];
    }
}
