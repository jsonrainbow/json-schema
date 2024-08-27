<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Iterators;

use JsonSchema\Iterator\ObjectIterator;
use PHPUnit\Framework\TestCase;

class ObjectIteratorTest extends TestCase
{
    protected $testObject;

    public function setUp(): void
    {
        $this->testObject = (object) [
            'subOne' => (object) [
                'propertyOne' => 'valueOne',
                'propertyTwo' => 'valueTwo',
                'propertyThree' => 'valueThree'
            ],
            'subTwo' => (object) [
                'propertyFour' => 'valueFour',
                'subThree' => (object) [
                    'propertyFive' => 'valueFive',
                    'propertySix' => 'valueSix'
                ]
            ],
            'propertySeven' => 'valueSeven'
        ];
    }

    public function testCreate(): void
    {
        $i = new ObjectIterator($this->testObject);

        $this->assertInstanceOf('\JsonSchema\Iterator\ObjectIterator', $i);
    }

    public function testInitialState(): void
    {
        $i = new ObjectIterator($this->testObject);

        $this->assertEquals($this->testObject, $i->current());
    }

    public function testCount(): void
    {
        $i = new ObjectIterator($this->testObject);

        $this->assertEquals(4, $i->count());
    }

    public function testKey(): void
    {
        $i = new ObjectIterator($this->testObject);

        while ($i->key() != 2) {
            $i->next();
        }

        $this->assertEquals($this->testObject->subTwo->subThree, $i->current());
    }

    public function testAlwaysObjects(): void
    {
        $i= new ObjectIterator($this->testObject);

        foreach ($i as $item) {
            $this->assertInstanceOf('\StdClass', $item);
        }
    }

    public function testReachesAllProperties(): void
    {
        $i = new ObjectIterator($this->testObject);

        $count = 0;
        foreach ($i as $item) {
            $count += count(get_object_vars($item));
        }

        $this->assertEquals(10, $count);
    }
}
