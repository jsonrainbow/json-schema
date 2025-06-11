<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\Constraints\FormatConstraint;

class FormatTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function setUp(): void
    {
        date_default_timezone_set('UTC');
    }

    public function testNullThing(): void
    {
        $validator = new FormatConstraint();
        $schema = new \stdClass();

        $checkValue = 10;
        $validator->check($checkValue, $schema);
        $this->assertEmpty($validator->getErrors());
    }

    public function testRegex(): void
    {
        $validator = new FormatConstraint();
        $schema = new \stdClass();
        $schema->format = 'regex';

        $validator->reset();
        $checkValue = '\d+';
        $validator->check($checkValue, $schema);
        $this->assertEmpty($validator->getErrors());

        $validator->reset();
        $checkValue = '^(abc]';
        $validator->check($checkValue, $schema);
        $this->assertCount(1, $validator->getErrors());

        $validator->reset();
        $checkValue = '^猡猡獛$';
        $validator->check($checkValue, $schema);
        $this->assertEmpty($validator->getErrors());
    }

    /**
     * @dataProvider getValidFormats
     */
    public function testValidFormat($string, $format): void
    {
        $validator = new FormatConstraint();
        $schema = new \stdClass();
        $schema->format = $format;

        $validator->check($string, $schema);
        $this->assertEmpty($validator->getErrors());
    }

    /**
     * @dataProvider getInvalidFormats
     */
    public function testInvalidFormat($string, $format): void
    {
        $validator = new FormatConstraint();
        $schema = new \stdClass();
        $schema->format = $format;

        $validator->check($string, $schema);
        $this->assertCount(1, $validator->getErrors(), 'Expected 1 error');
    }

    /**
     * @dataProvider getInvalidFormats
     */
    public function testDisabledFormat($string, $format): void
    {
        $factory = new Factory();
        $validator = new FormatConstraint($factory);
        $schema = new \stdClass();
        $schema->format = $format;
        $factory->addConfig(Constraint::CHECK_MODE_DISABLE_FORMAT);

        $validator->check($string, $schema);
        $this->assertEmpty($validator->getErrors());
    }

    public function getValidFormats(): \Generator
    {
        yield ['2001-01-23', 'date'];
        yield ['2000-02-29', 'date'];
        yield [42, 'date'];
        yield [4.2, 'date'];

        yield ['12:22:01', 'time'];
        yield ['00:00:00', 'time'];
        yield ['23:59:59', 'time'];
        yield [42, 'time'];
        yield [4.2, 'time'];

        yield ['2000-05-01T12:12:12Z', 'date-time'];
        yield ['2000-05-01T12:12:12+0100', 'date-time'];
        yield ['2000-05-01T12:12:12+01:00', 'date-time'];
        yield ['2000-05-01T12:12:12.123456Z', 'date-time'];
        yield ['2000-05-01T12:12:12.123Z', 'date-time'];
        yield ['2000-05-01T12:12:12.123000Z', 'date-time'];
        yield ['2000-05-01T12:12:12.0Z', 'date-time'];
        yield ['2000-05-01T12:12:12.000Z', 'date-time'];
        yield ['2000-05-01T12:12:12.000000Z', 'date-time'];
        yield [42, 'date-time'];
        yield [4.2, 'date-time'];

        yield ['0', 'utc-millisec'];

        yield ['aqua', 'color'];
        yield ['black', 'color'];
        yield ['blue', 'color'];
        yield ['fuchsia', 'color'];
        yield ['gray', 'color'];
        yield ['green', 'color'];
        yield ['lime', 'color'];
        yield ['maroon', 'color'];
        yield ['navy', 'color'];
        yield ['olive', 'color'];
        yield ['orange', 'color'];
        yield ['purple', 'color'];
        yield ['red', 'color'];
        yield ['silver', 'color'];
        yield ['teal', 'color'];
        yield ['white', 'color'];
        yield ['yellow', 'color'];
        yield ['#fff', 'color'];
        yield ['#00cc00', 'color'];
        yield [42, 'color'];
        yield [4.2, 'color'];

        yield ['background: blue', 'style'];
        yield ['color: #000;', 'style'];

        yield ['555 320 1212', 'phone'];

        yield ['http://bluebox.org', 'uri'];
        yield ['//bluebox.org', 'uri-reference'];
        yield ['/absolutePathReference/', 'uri-reference'];
        yield ['./relativePathReference/', 'uri-reference'];
        yield ['./relative:PathReference/', 'uri-reference'];
        yield ['relativePathReference/', 'uri-reference'];
        yield ['relative/Path:Reference/', 'uri-reference'];
        yield [42, 'uri-reference'];
        yield [4.2, 'uri-reference'];

        yield ['info@something.edu', 'email'];
        yield [42, 'email'];
        yield [4.2, 'email'];

        yield ['10.10.10.10', 'ip-address'];
        yield ['127.0.0.1', 'ip-address'];
        yield [42, 'ip-address'];
        yield [4.2, 'ip-address'];

        yield ['127.0.0.1', 'ipv4'];
        yield [42, 'ipv4'];
        yield [4.2, 'ipv4'];

        yield ['::ff', 'ipv6'];
        yield [42, 'ipv6'];
        yield [4.2, 'ipv6'];

        yield ['www.example.com', 'host-name'];
        yield ['3v4l.org', 'host-name'];
        yield ['a-valid-host.com', 'host-name'];
        yield ['localhost', 'host-name'];
        yield [42, 'host-name'];
        yield [4.2, 'host-name'];

        yield ['www.example.com', 'hostname'];
        yield ['3v4l.org', 'hostname'];
        yield ['a-valid-host.com', 'hostname'];
        yield ['localhost', 'hostname'];
        yield [42, 'hostname'];
        yield [4.2, 'hostname'];

        yield ['anything', '*'];
        yield ['unknown', '*'];
    }

    public function getInvalidFormats(): \Generator
    {
        yield ['January 1st, 1910', 'date'];
        yield ['199-01-1', 'date'];
        yield ['2012-0-11', 'date'];
        yield ['2012-10-1', 'date'];

        yield ['24:01:00', 'time'];
        yield ['00:00:60', 'time'];
        yield ['25:00:00', 'time'];

        yield ['invalid_value_2000-05-01T12:12:12Z', 'date-time'];
        yield ['2000-05-01T12:12:12Z_invalid_value', 'date-time'];
        yield ['1999-1-11T00:00:00Z', 'date-time'];
        yield ['1999-01-11T00:00:00+100', 'date-time'];
        yield ['1999-01-11T00:00:00+1:00', 'date-time'];
        yield ['1999.000Z-01-11T00:00:00+1:00', 'date-time'];

        yield [PHP_INT_MAX, 'utc-millisec'];

        yield ['grey', 'color'];
        yield ['#HHH', 'color'];
        yield ['#000a', 'color'];
        yield ['#aa', 'color'];

        yield ['background; blue', 'style'];

        yield ['1 123 4424', 'phone'];

        yield ['htt:/bluebox.org', 'uri'];
        yield ['.relative:path/reference/', 'uri'];
        yield ['', 'uri'];
        yield ['//bluebox.org', 'uri'];
        yield ['/absolutePathReference/', 'uri'];
        yield ['./relativePathReference/', 'uri'];
        yield ['./relative:PathReference/', 'uri'];
        yield ['relativePathReference/', 'uri'];
        yield ['relative/Path:Reference/', 'uri'];

        yield ['info@somewhere', 'email'];

        yield ['256.2.2.2', 'ip-address'];

        yield [':::ff', 'ipv6'];

        yield ['@localhost', 'host-name'];
        yield ['..nohost', 'host-name'];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{ "counter": "10" }',
            '{
                "type": "object",
                "properties": {
                    "counter": {
                        "type": "string",
                        "format": "regex",
                        "pattern": "[0-9]+"
                    }
                }
            }'
        ];
    }

    public function getInvalidTests(): \Generator
    {
        yield [
            '{ "counter": "blue" }',
            '{
                "type": "object",
                "properties": {
                    "counter": {
                        "type": "string",
                        "format": "regex",
                        "pattern": "[0-9]+"
                    }
                }
            }'
        ];
        yield [
            '{ "color": "blueberry" }',
            '{
                "type": "object",
                "properties": {
                    "color": {
                        "type": "string",
                        "format": "color"
                    }
                }
            }'
        ];
    }
}
