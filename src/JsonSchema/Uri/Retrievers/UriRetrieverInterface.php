<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Uri\Retrievers;

/**
 * Interface for URI retrievers
 * 
 * @author Sander Coolen <sander@jibber.nl> 
 */
interface UriRetrieverInterface
{
    public function retrieve($uri);
    
    public function getContentType();
}