<?php

namespace JsonSchema\Tests;

use JsonSchema\Rfc3339;
use PHPUnit\Framework\TestCase;

class Rfc3339Test extends TestCase
{
    /**
     * @param string         $string
     * @param \DateTime|null $expected
     * @dataProvider provideValidFormats
     */
    public function testCreateFromValidString($string, \DateTime $expected)
    {
        $actual = Rfc3339::createFromString($string);

        $this->assertInstanceOf('DateTime', $actual);
        $this->assertEquals($expected->format('U.u'), $actual->format('U.u'));
    }

    /**
     * @param string $string
     * @dataProvider provideInvalidFormats
     */
    public function testCreateFromInvalidString($string)
    {
        $this->assertNull(Rfc3339::createFromString($string), sprintf('String "%s" should not be converted to DateTime', $string));
    }

    public function provideValidFormats()
    {
        return array(
            array(
                '2000-05-01T12:12:12Z',
                \DateTime::createFromFormat('Y-m-d\TH:i:s', '2000-05-01T12:12:12', new \DateTimeZone('UTC'))
            ),
            array(
                '2000-05-01T12:12:12+0100',
                \DateTime::createFromFormat('Y-m-d\TH:i:sP', '2000-05-01T12:12:12+01:00')
            ),
            array(
                '2000-05-01T12:12:12+01:00',
                \DateTime::createFromFormat('Y-m-d\TH:i:sP', '2000-05-01T12:12:12+01:00')
            ),
            array(
                '2000-05-01T12:12:12.123456Z',
                \DateTime::createFromFormat('Y-m-d\TH:i:s.u', '2000-05-01T12:12:12.123456', new \DateTimeZone('UTC'))
            ),
            array(
                '2000-05-01T12:12:12.123Z',
                \DateTime::createFromFormat('Y-m-d\TH:i:s.u', '2000-05-01T12:12:12.123000', new \DateTimeZone('UTC'))
            ),
            array(
                '2000-05-01 12:12:12.123Z',
                \DateTime::createFromFormat('Y-m-d H:i:s.u', '2000-05-01 12:12:12.123000', new \DateTimeZone('UTC'))
            ),
            array(
                '2000-05-01 12:12:12.123456Z',
                \DateTime::createFromFormat('Y-m-d H:i:s.u', '2000-05-01 12:12:12.123456', new \DateTimeZone('UTC'))
            )
        );
    }

    public function provideInvalidFormats()
    {
        return array(
            array('1999-1-11T00:00:00Z'),
            array('1999-01-11T00:00:00+100'),
            array('1999-01-11T00:00:00+1:00'),
            array('1999-01-01  00:00:00Z'),
            array('1999-1-11 00:00:00Z')
        );
    }
}
