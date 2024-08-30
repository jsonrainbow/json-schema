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

    public function getValidFormats(): array
    {
        return [
            ['2001-01-23', 'date'],
            ['2000-02-29', 'date'],

            ['12:22:01', 'time'],
            ['00:00:00', 'time'],
            ['23:59:59', 'time'],

            ['2000-05-01T12:12:12Z', 'date-time'],
            ['2000-05-01T12:12:12+0100', 'date-time'],
            ['2000-05-01T12:12:12+01:00', 'date-time'],
            ['2000-05-01T12:12:12.123456Z', 'date-time'],
            ['2000-05-01T12:12:12.123Z', 'date-time'],
            ['2000-05-01T12:12:12.123000Z', 'date-time'],
            ['2000-05-01T12:12:12.0Z', 'date-time'],
            ['2000-05-01T12:12:12.000Z', 'date-time'],
            ['2000-05-01T12:12:12.000000Z', 'date-time'],

            ['0', 'utc-millisec'],

            ['aqua', 'color'],
            ['black', 'color'],
            ['blue', 'color'],
            ['fuchsia', 'color'],
            ['gray', 'color'],
            ['green', 'color'],
            ['lime', 'color'],
            ['maroon', 'color'],
            ['navy', 'color'],
            ['olive', 'color'],
            ['orange', 'color'],
            ['purple', 'color'],
            ['red', 'color'],
            ['silver', 'color'],
            ['teal', 'color'],
            ['white', 'color'],
            ['yellow', 'color'],
            ['#fff', 'color'],
            ['#00cc00', 'color'],

            ['background: blue', 'style'],
            ['color: #000;', 'style'],

            ['555 320 1212', 'phone'],

            ['http://bluebox.org', 'uri'],
            ['//bluebox.org', 'uri-reference'],
            ['/absolutePathReference/', 'uri-reference'],
            ['./relativePathReference/', 'uri-reference'],
            ['./relative:PathReference/', 'uri-reference'],
            ['relativePathReference/', 'uri-reference'],
            ['relative/Path:Reference/', 'uri-reference'],

            ['info@something.edu', 'email'],

            ['10.10.10.10', 'ip-address'],
            ['127.0.0.1', 'ip-address'],

            ['::ff', 'ipv6'],

            ['www.example.com', 'host-name'],
            ['3v4l.org', 'host-name'],
            ['a-valid-host.com', 'host-name'],
            ['localhost', 'host-name'],

            ['anything', '*'],
            ['unknown', '*'],
        ];
    }

    public function getInvalidFormats(): array
    {
        return [
            ['January 1st, 1910', 'date'],
            ['199-01-1', 'date'],
            ['2012-0-11', 'date'],
            ['2012-10-1', 'date'],

            ['24:01:00', 'time'],
            ['00:00:60', 'time'],
            ['25:00:00', 'time'],

            ['invalid_value_2000-05-01T12:12:12Z', 'date-time'],
            ['2000-05-01T12:12:12Z_invalid_value', 'date-time'],
            ['1999-1-11T00:00:00Z', 'date-time'],
            ['1999-01-11T00:00:00+100', 'date-time'],
            ['1999-01-11T00:00:00+1:00', 'date-time'],
            ['1999.000Z-01-11T00:00:00+1:00', 'date-time'],

            [PHP_INT_MAX, 'utc-millisec'],

            ['grey', 'color'],
            ['#HHH', 'color'],
            ['#000a', 'color'],
            ['#aa', 'color'],

            ['background; blue', 'style'],

            ['1 123 4424', 'phone'],

            ['htt:/bluebox.org', 'uri'],
            ['.relative:path/reference/', 'uri'],
            ['', 'uri'],
            ['//bluebox.org', 'uri'],
            ['/absolutePathReference/', 'uri'],
            ['./relativePathReference/', 'uri'],
            ['./relative:PathReference/', 'uri'],
            ['relativePathReference/', 'uri'],
            ['relative/Path:Reference/', 'uri'],

            ['info@somewhere', 'email'],

            ['256.2.2.2', 'ip-address'],

            [':::ff', 'ipv6'],

            ['@localhost', 'host-name'],
            ['..nohost', 'host-name'],
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
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
                }'],
        ];
    }

    public function getInvalidTests(): array
    {
        return [
            [
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
            ],
            [
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
            ]
        ];
    }
}
