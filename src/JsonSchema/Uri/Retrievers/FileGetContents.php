<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Uri\Retrievers;

use JsonSchema\Validator;

/**
 * Tries to retrieve JSON schemas from a URI using file_get_contents()
 * 
 * @author Sander Coolen <sander@jibber.nl> 
 */
class FileGetContents implements UriRetrieverInterface
{
    protected $contentType;
    protected $messageBody;
    
    public function retrieve($uri)
    {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => "Accept: " . Validator::SCHEMA_MEDIA_TYPE
            )));
        
        $response = file_get_contents($uri);
        if (false === $response) {
            throw new ResourceNotFoundException('JSON schema not found');
        }
        
        $this->messageBody = $response;
		if (! empty($http_response_header)) {
			$this->fetchContentType($http_response_header);
		} else {
			// Could be a "file://" url or something else - fake up the response
			$this->contentType = null;
		}
        
        return $this->messageBody;
    }
    
    /**
     * @param array $headers HTTP Response Headers
     * @return boolean Whether the Content-Type header was found or not
     */
    private function fetchContentType(array $headers)
    {
        foreach ($headers as $header) {
            if ($this->contentType = self::getContentTypeMatchInHeader($header)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param string $header
     * @return string|null
     */
    protected static function getContentTypeMatchInHeader($header)
    {
        if (0 < preg_match("/Content-Type:(\V*)/ims", $header, $match)) {
            return trim($match[1]);
        }
    }

    public function getContentType()
    {
        return $this->contentType;
    }
}
