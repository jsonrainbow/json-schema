<?php

namespace JsonSchema\Tests\Uri\Retrievers
{
    use JsonSchema\Uri\Retrievers\Curl;
    use PHPUnit\Framework\TestCase;

    class CurlTest extends TestCase
    {
        public function testRetrieveFile()
        {
            $c = new Curl();
            $c->retrieve(realpath(__DIR__ . '/../../fixtures/foobar.json'));
        }

        public function testRetrieveNonexistantFile()
        {
            $c = new Curl();

            $this->setExpectedException(
                '\JsonSchema\Exception\ResourceNotFoundException',
                'JSON schema not found'
            );
            $c->retrieve(__DIR__ . '/notARealFile');
        }

        public function testNoContentType()
        {
            $c = new Curl();
            $c->retrieve(realpath(__DIR__ . '/../../fixtures') . '/foobar-noheader.json');
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
            $headers = implode("\n", array(
                'Content-Type: application/json'
            ));

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
