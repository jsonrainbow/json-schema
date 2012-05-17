<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Uri;

/**
 * Resolves JSON Schema URIs
 * 
 * @author Sander Coolen <sander@jibber.nl> 
 */
class UriResolver
{
    public function parse($uri)
    {
        preg_match('|^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?|', $uri, $match);

        $components = array();
        if (5 < count($match)) {
            $components =  array(
                'scheme'    => $match[2],
                'host'      => $match[4],
                'authority' => $match[5]
            );
        } 
        if (7 < count($match)) {
            $components['query'] = $match[7];
        }
        if (9 < count($match)) {
            $components['fragment'] = $match[9];
        }
        
        return $components;
    }
    
    public function isValid($uri)
    {
        $components = $this->parse($uri);
        
        return !empty($components);
    }
}