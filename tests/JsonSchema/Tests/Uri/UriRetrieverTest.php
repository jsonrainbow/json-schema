<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Uri;

use JsonSchema\Validator;

class UriRetrieverTest extends \PHPUnit_Framework_TestCase
{
    protected $validator;

    protected function setUp()
    {
        $this->validator = new Validator();
    }
    
    private function getCurlRetrieverMock($returnSchema, $returnMediaType = Validator::SCHEMA_MEDIA_TYPE)
    {
        $curlRetriever = $this->getMock('JsonSchema\Uri\Retrievers\Curl', array('retrieve', 'getContentType'));
        
        $curlRetriever->expects($this->once())
                      ->method('retrieve')
                      ->with($this->equalTo('http://some.host.at/somewhere/parent'))
                      ->will($this->returnValue($returnSchema));
        
        $curlRetriever->expects($this->once())
                      ->method('getContentType')
                      ->will($this->returnValue($returnMediaType));
        
        return $curlRetriever;
    }
    
    /**
     * @dataProvider jsonProvider 
     */
    public function testChildExtendsParent($childSchema, $parentSchema)
    {
        $curlRetrieverMock = $this->getCurlRetrieverMock($parentSchema);
        
        Validator::setUriRetriever($curlRetrieverMock);
        
        $json = '{"childProp":"infant", "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);
        
        $this->validator->check($decodedJson, $decodedJsonSchema);
        $this->assertTrue($this->validator->isValid());
    }
    
    /**
     * @dataProvider jsonProvider 
     */
    public function testResolveRelativeUri()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * @dataProvider jsonProvider
     * @expectedException JsonSchema\Exception\InvalidSchemaMediaTypeException
     */
    public function testInvalidSchemaMediaType($childSchema, $parentSchema)
    {
        $curlRetrieverMock = $this->getCurlRetrieverMock($parentSchema, 'text/html');
        
        Validator::setUriRetriever($curlRetrieverMock);
        
        $json = '{}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);
        
        $this->validator->check($decodedJson, $decodedJsonSchema);
    }
    
    /**
     * @dataProvider jsonProvider
     * @expectedException JsonSchema\Exception\JsonDecodingException
     */
    public function testParentJsonError($childSchema, $parentSchema)
    {
        $curlRetrieverMock = $this->getCurlRetrieverMock('<html>', 'application/schema+json');
        
        Validator::setUriRetriever($curlRetrieverMock);
        
        $json = '{}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);
        
        $this->validator->check($decodedJson, $decodedJsonSchema);
    }
    
    public function jsonProvider()
    {
        $childSchema = <<<EOF
{
    "type":"object",    
    "title":"child",
    "extends":"http://some.host.at/somewhere/parent",
    "properties":
    {
        "childProp":
        {
            "type":"string"
        }
    }
}
EOF;
        $parentSchema = <<<EOF
{
    "type":"object",    
    "title":"parent",
    "properties":
    {
        "parentProp":
        {
            "type":"boolean"
        }
    }
}
EOF;
        return array(
            array($childSchema, $parentSchema)
        );
    }
}
