<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Uri\Retrievers
{
    use JsonSchema\Uri\Retrievers\Curl;
    use PHPUnit\Framework\TestCase;

    class CurlTest extends TestCase
    {
        public function testRetrieveFile(): void
        {
            $c = new Curl();
            $result = $c->retrieve(realpath(__DIR__ . '/../../fixtures/foobar.json'));

            self::assertStringEqualsFileCanonicalizing(realpath(__DIR__ . '/../../fixtures/foobar.json'), $result);
        }

        public function testRetrieveNonexistantFile(): void
        {
            $c = new Curl();

            $this->expectException('\JsonSchema\Exception\ResourceNotFoundException');
            $this->expectExceptionMessage('JSON schema not found');

            $c->retrieve(__DIR__ . '/notARealFile');
        }

        public function testNoContentType(): void
        {
            $c = new Curl();
            $result = $c->retrieve(realpath(__DIR__ . '/../../fixtures') . '/foobar-noheader.json');

            self::assertStringEqualsFileCanonicalizing(realpath(__DIR__ . '/../../fixtures/foobar.json'), $result);
        }
    }
}

namespace JsonSchema\Uri\Retrievers
{
    function curl_exec($curl)
    {
        $uri = curl_getinfo($curl, \CURLINFO_EFFECTIVE_URL);

        if ($uri === realpath(__DIR__ . '/../../fixtures/foobar.json')) {
            // return file with headers
            $headers = implode("\n", [
                'Content-Type: application/json'
            ]);

            return sprintf("%s\r\n\r\n%s", $headers, file_get_contents($uri));
        } elseif ($uri === realpath(__DIR__ . '/../../fixtures') . '/foobar-noheader.json') {
            // return file without headers
            $uri = realpath(__DIR__ . '/../../fixtures/foobar.json');

            return "\r\n\r\n" . file_get_contents($uri);
        }

        // fallback to real curl_exec
        return \curl_exec($curl);
    }
}
