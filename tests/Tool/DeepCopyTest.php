<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Tool;

use JsonSchema\Tool\DeepCopy;
use PHPUnit\Framework\TestCase;

class DeepCopyTest extends TestCase
{
    public function testCanDeepCopyObject(): void
    {
        $input = (object) ['foo' => 'bar'];

        $result = DeepCopy::copyOf($input);

        self::assertEquals($input, $result);
        self::assertNotSame($input, $result);
    }

    public function testCanDeepCopyObjectWithChildObject(): void
    {
        $child = (object) ['bar' => 'baz'];
        $input = (object) ['foo' => $child];

        $result = DeepCopy::copyOf($input);

        self::assertEquals($input, $result);
        self::assertNotSame($input, $result);
        self::assertEquals($input->foo, $result->foo);
        self::assertNotSame($input->foo, $result->foo);
    }

    public function testCanDeepCopyArray(): void
    {
        $input = ['foo' => 'bar'];

        $result = DeepCopy::copyOf($input);

        self::assertEquals($input, $result);
    }

    public function testCanDeepCopyArrayWithNestedArray(): void
    {
        $nested = ['bar' => 'baz'];
        $input = ['foo' => $nested];

        $result = DeepCopy::copyOf($input);

        self::assertEquals($input, $result);
    }
}
