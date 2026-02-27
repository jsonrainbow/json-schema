<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\JsonDecodingException;
use PHPUnit\Framework\TestCase;

class JsonDecodingExceptionTest extends TestCase
{
    public function testHierarchy()
    {
        $exception = new JsonDecodingException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }

    public function testDefaultMessage()
    {
        $exception = new JsonDecodingException();
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorNoneMessage()
    {
        $exception = new JsonDecodingException(JSON_ERROR_NONE);
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorDepthMessage()
    {
        $exception = new JsonDecodingException(JSON_ERROR_DEPTH);
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorStateMismatchMessage()
    {
        $exception = new JsonDecodingException(JSON_ERROR_STATE_MISMATCH);
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorControlCharacterMessage()
    {
        $exception = new JsonDecodingException(JSON_ERROR_CTRL_CHAR);
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorUtf8Message()
    {
        $exception = new JsonDecodingException(JSON_ERROR_UTF8);
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorSyntaxMessage()
    {
        $exception = new JsonDecodingException(JSON_ERROR_SYNTAX);
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorInfiniteOrNotANumberMessage()
    {
        if (!defined('JSON_ERROR_INF_OR_NAN')) {
            self::markTestSkipped('JSON_ERROR_INF_OR_NAN is not defined until php55.');
        }

        $exception = new JsonDecodingException(JSON_ERROR_INF_OR_NAN);
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorRecursionMessage()
    {
        if (!defined('JSON_ERROR_RECURSION')) {
            self::markTestSkipped('JSON_ERROR_RECURSION is not defined until php55.');
        }

        $exception = new JsonDecodingException(JSON_ERROR_RECURSION);
        self::assertNotEmpty($exception->getMessage());
    }

    public function testErrorUnsupportedTypeMessage()
    {
        if (!defined('JSON_ERROR_UNSUPPORTED_TYPE')) {
            self::markTestSkipped('JSON_ERROR_UNSUPPORTED_TYPE is not defined until php55.');
        }

        $exception = new JsonDecodingException(JSON_ERROR_UNSUPPORTED_TYPE);
        self::assertNotEmpty($exception->getMessage());
    }
}
